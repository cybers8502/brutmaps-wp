<?php

namespace Services;

use Exception;

class MailchimpService
{
    /**
     * Subscribe the given email to Mailchimp list.
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return bool
     * @throws Exception
     */
    public function subscribe(string $email, string $firstName = '', string $lastName = ''): bool
    {
        if (!is_email($email)) {
            throw new Exception('Invalid email address.');
        }

        $mergeFields = [];

        if ($firstName) {
            $mergeFields['FNAME'] = $firstName;
        }

        if ($lastName) {
            $mergeFields['LNAME'] = $lastName;
        }

        try {
            if (!function_exists('mailchimp_member_data_update')) {
                throw new Exception('Mailchimp function not available.');
            }

            add_filter('mailchimp_get_ecommerce_merge_tags', function () use ($mergeFields) {
                return $mergeFields;
            });

            mailchimp_member_data_update($email, null, 'custom', 'subscribed', $mergeFields);
            return true;
        } catch (Exception $e) {
            throw new Exception("Mailchimp subscribe failed: " . $e->getMessage());
        }
    }

    /**
     * Unsubscribe the given email from Mailchimp list.
     *
     * @param string $email
     * @return bool
     * @throws Exception
     */
    public function unsubscribe(string $email): bool
    {
        if (!is_email($email)) {
            throw new Exception('Invalid email address.');
        }

        try {
            if (!function_exists('mailchimp_member_data_update')) {
                throw new Exception('Mailchimp function not available.');
            }

            mailchimp_member_data_update($email, null, 'custom', 'unsubscribed');
            return true;
        } catch (Exception $e) {
            throw new Exception("Mailchimp unsubscribe failed: " . $e->getMessage());
        }
    }

    /**
     * Check if an email is subscribed in Mailchimp.
     *
     * @param string $email
     * @return bool
     */
    public function isSubscribed(string $email): bool
    {
        if (!function_exists('mailchimp_get_subscriber_status')) {
            return false;
        }

        $status = mailchimp_get_subscriber_status($email);
        return $status === 'subscribed';
    }
}
