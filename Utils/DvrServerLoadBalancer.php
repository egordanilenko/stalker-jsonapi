<?php
/**
 * Created by PhpStorm.
 * User: gsv
 * Date: 18.01.18
 * Time: 17:08
 */

namespace Utils;


use Exception\StorageSessionLimitException;
use Model\Channel;

class DvrServerLoadBalancer
{

    private $channel;
    private $config;

    private $storageList;
    private $storage;

    /**
     * DvrServerLoadBalancer constructor.
     * @param Channel $channel
     */
    public function __construct($channel, $config, $device)
    {
        $this->channel = $channel;
        $this->config = $config;
        //var_dump($this->config);
        $this->storageList = $this->getAllActiveStorages();

        $this->storage = $this->getLessLoadedTaskByChId($channel->getId());
        $this->channel->setStorage($this->storage);

        $count_device = ORM::for_table('tvip_device_storage')->where(['channel_id' => $this->channel->getId(), 'device_id' => $device->getId()])->count();

        if (!empty($this->storage)) {
            

            if ($count_device > 0) {
                $device_storage = ORM::for_table('tvip_device_storage')->where(['channel_id' => $this->channel->getId(), 'device_id' => $device->getId()])->find_one();
                $device_storage->storage_name = $this->storage->storage_name;
                $device_storage->timestamp = date('Y-m-d H:s:i',time());
                $device_storage->save();
            } else {

                $device_id = $device->getId();
                $channel_id = $this->channel->getId();
                $storage_name = $this->storage->storage_name;
                ORM::raw_execute("INSERT INTO tvip_device_storage (device_id,storage_name,channel_id) VALUES ($device_id,'$storage_name',$channel_id)");
            }
        }
    }

    public function getDvrStorage()
    {
        return $this->storage;
    }


    private function getLessLoadedTaskByChId($ch_id, $ignore_session_limit = false)
    {

        $tasks = ORM::for_table('tv_archive')->where(['ch_id' => $ch_id])->find_many();

        $tasks_map = array();

        foreach ($tasks as $task) {
            $tasks_map[$task->storage_name] = $task;
        }

        $all_storages = array_keys($this->storageList);
        $task_storages = array_keys($tasks_map);

        $intersection = array_intersect($all_storages, $task_storages);
        $intersection = array_values($intersection);

        if (empty($intersection)) {
            return false;
        }

        if ($this->storageList[$intersection[0]]['load'] >= 1 && !$ignore_session_limit) {
            $this->incrementStorageDeny($intersection[0]);
            throw new StorageSessionLimitException($intersection[0]);
        }

        $task = $tasks_map[$intersection[0]];

        $storage = ORM::for_table('storages')->where(['storage_name' => $task->storage_name])->find_one();

        return $storage;
    }

    /**
     * Increment counter of storage deny
     *
     * @param string $storage_name
     */
    protected function incrementStorageDeny($storage_name)
    {

        $storage = ORM::for_table('storage_deny')->where(['name' => $storage_name])->find_one();

        if (empty($storage)) {
            $storage_deny = ORM::for_table('storage_deny')->create();
            $storage_deny->name = $storage_name;
            $storage_deny->counter = 1;
            $storage_deny->set_expr('updated', 'NOW()');
            $storage_deny->save();
        } else {
            $storage->name = $storage_name;
            $storage->counter = $storage->counter + 1;
            $storage->set_expr('updated', 'NOW()');
            $storage->save();
        }
    }

    /**
     * Get from database all active storages
     *
     * @return array active storages
     */
    protected function getAllActiveStorages()
    {

        $storages = array();

        $data = ORM::for_table('storages')->where_any_is([
            ['status' => 1, 'for_records' => 1]
            /* ['stream_server_type' => NULL,'status' => 1, 'for_records' => 1],
             ['stream_server_type' => '','status' => 1, 'for_records' => 1]*/
        ])->find_many();


        foreach ($data as $idx => $storage) {
            $storages[$storage['storage_name']] = $storage;
            $storages[$storage['storage_name']]['load'] = $this->getStorageLoad($storage);
        }

        $storages = $this->sortByLoad($storages);

        return $storages;
    }

    /**
     * Calculates storage load
     *
     * @param array $storage_name
     * @return int storage load
     */
    protected function getStorageLoad($storage)
    {

        if ($storage->max_online > 0) {
            return $this->getStorageOnline($storage->storage_name) / $storage->max_online;
        }
        return 1;
    }

    /**
     * Return online sessions on storage
     *
     * @param string $storage_name
     * @return int sessions online
     */
    protected function getStorageOnline($storage_name)
    {


        $vclub_sd_sessions = ORM::for_table('users')->where([
            'now_playing_type' => 2,
            'hd_content' => 0,
            'storage_name' => $storage_name,
            'keep_alive' => date("Y-m-d H:i:s", time() - $this->config['watchdog_timeout'] * 2)
        ], ['keep_alive' => '>'])->count();

        $vclub_hd_sessions = ORM::for_table('users')->where([
            'now_playing_type' => 2,
            'hd_content' => 1,
            'storage_name' => $storage_name,
            'keep_alive' => date("Y-m-d H:i:s", time() - $this->config['watchdog_timeout'] * 2)
        ], ['keep_alive' => '>'])->count();

        $pvr_rec_sessions = ORM::for_table('rec_files')->where(['storage_name' => $storage_name, 'ended' => 0])->count();

        $archive_rec_sessions = ORM::for_table('tv_archive')->where(['storage_name' => $storage_name])->count();


        $archive_sessions = ORM::for_table('users')->where([
            'now_playing_type' => 11,
            'hd_content' => 0,
            'storage_name' => $storage_name,
            'keep_alive' => date("Y-m-d H:i:s", time() - $this->config['watchdog_timeout'] * 2)
        ], ['keep_alive' => '>'])->count();


        return $vclub_sd_sessions + 3 * $vclub_hd_sessions + $pvr_rec_sessions + $archive_rec_sessions + $archive_sessions;
    }

    /**
     * Sort array of good storages by load
     *
     * @param array $storages
     * @return array good storages sorted by load
     */
    protected function sortByLoad($storages)
    {

        if (!empty($storages)) {

            foreach ($storages as $name => $storage) {
                $load[$name] = $storage['load'];
            }

            array_multisort($load, SORT_ASC, SORT_NUMERIC, $storages);
        }

        return $storages;
    }

    public function getChannel()
    {
        return $this->channel;
    }
}