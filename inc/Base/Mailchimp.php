<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

use MailchimpMarketing\ApiClient;

class Mailchimp{

    public function __construct()
    {
        $mailchimp = new ApiClient();

        $this->mailchimp = $mailchimp->setConfig([
            'apiKey' => '57abcff4910172c6ba5677053f22bbc4-us21',
            'server' => 'us21',
        ]);
    }

    public function test(){
        $response = $this->mailchimp->ping->get();
        print_r($response);
    }

    public function tagUpdate($email, $tags){

       $response = $this->mailchimp->lists->updateListMemberTags("53d2f94317", $email, [
            "tags" => $tags
        ]);

    }

    public function addContact($email){

        $this->mailchimp->lists->setListMember("53d2f94317", md5(strtolower($email)), [
            "email_address" => $email,
            "status_if_new" => "subscribed",
        ]);
    }


}