<?php

namespace App\MessageHandler;

use App\Manager\CallManager;
use App\Message\EnrichExecuteMessage;
use App\Model\CallRequestModel;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EnrichExecuteMessageHandler implements MessageHandlerInterface
{
    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function __invoke(EnrichExecuteMessage $message)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_enrich/policy/'.$message->getName().'/_execute');
        $this->callManager->call($callRequest);
    }
}
