<?php
namespace Jivoo\Http\Message;

class RequestTest extends MessageTest
{
    protected function getInstance()
    {
        return new Request(new Uri('/'));
    }
}
