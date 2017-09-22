<?php

namespace Response;


use Model\Command;
use Type\MessageType;
use Type\PollType;

class MessagesResponse implements ResponseInterface
{
    /**
     * @var PollType
     */
    public $poll;

    /**
     * @var MessageType[]
     */
    public $messages=array();

    /**
     * MessagesResponse constructor.
     * @param PollType $poll
     * @param Command[] $commands
     */
    public function __construct(PollType $poll, array $commands)
    {
        $this->poll = $poll;
        foreach($commands as $command){
            if(array_key_exists($command->getEvent(),MessageType::$mapping)){
                array_push($this->messages,new MessageType($command));
            }
        }
    }

}