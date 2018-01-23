<?php

class Migration000
{
    function up()
    {
        global $migrate;
        $migrate->query('CREATE TABLE IF NOT EXISTS `tvip_device_storage` (
           id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
            device_id INT NOT NULL,
            storage_name VARCHAR(50) NOT NULL,
            channel_id INT NOT NULL,
            timestamp TIMESTAMP DEFAULT NOW() NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
    }

    function down()
    {
        global $migrate;
        $migrate->query('DROP TABLE IF EXISTS `tvip_device_storage`;');
    }
}

$migrate->up(array('Migration000', 'up'));
//$migrate->down(array(Migration000, 'down'));