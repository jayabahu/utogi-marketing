<?php

namespace UtogiMarketing\Campaign;


class CampaignDocuments
{
    
    public function addDownloadedContact()
    {
        $firstName = sanitize_text_field($_POST["firstName"]);
        $lastName = sanitize_text_field($_POST["lastName"]);
        $mobileNumber = sanitize_text_field($_POST["mobileNumber"]);
        $email = sanitize_text_field($_POST["email"]);
        $captcha = sanitize_text_field($_POST["captcha"]);
        $campaign = sanitize_text_field($_POST["campaign"]);
        $mutation = <<<JSON
        mutation {
            marketing {
                addDownloadedContact(
                    firstName: "$firstName",
                    lastName: "$lastName",
                    mobileNumber: "$mobileNumber",
                    email: "$email",
                    captcha: "$captcha",
                    campaign: "$campaign",
                ) {
                    success
                    data
                }
            }
        }
        JSON;

        $result = mutation($mutation, [], get_option('utogi_marketing-api-key'));

        echo json_encode($result);
        wp_die();
    }
}