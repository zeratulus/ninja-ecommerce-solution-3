<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 17.07.19
 * Time: 15:35
 */
class ModelToolGuestNewsletterEmails extends Model
{
    public function getEmails()
    {
        $results = $this->db->query("SELECT * FROM `".DB_PREFIX."guest_newsletter_emails`;");
        return !empty($results->rows);
    }

    public function addEmail($email)
    {
        $email = $this->db->escape($email);
        $this->db->query("INSERT INTO `".DB_PREFIX."guest_newsletter_emails`(`email`, `is_active`) VALUES ('{$email}', '1');");
        return true;
    }

    public function isEmailExists($email)
    {
        $email = $this->db->escape($email);
        $results = $this->db->query("SELECT * FROM `".DB_PREFIX."guest_newsletter_emails` WHERE email = '{$email}' LIMIT 1;");
        return !empty($results->rows);
    }

}