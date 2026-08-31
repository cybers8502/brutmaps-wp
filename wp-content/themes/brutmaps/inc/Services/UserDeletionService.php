<?php

namespace Brut\Services;

use Exception;
use WP_User;

class UserDeletionService
{
    /**
     * Основний метод для повного видалення користувача з сайту.
     *
     * @param int $user_id ID користувача, якого потрібно видалити.
     * @return void
     */
    public function delete(int $user_id): void
    {
        require_once ABSPATH . 'wp-admin/includes/user.php';

        // Отримуємо дані користувача
        $user = get_userdata($user_id);
        if (!$user instanceof WP_User) {
            return;
        }

        // Спочатку обробка пов’язаних даних
        $this->deleteFavorites($user_id);
        $this->deleteProfilePhoto($user_id);
        $this->deleteMetaData($user_id);
        $this->unsubscribeUser($user->user_email);

        // Видаляємо користувача з бази WordPress
        wp_delete_user($user_id);
    }

    /**
     * Видаляє всі метадані користувача з таблиці usermeta.
     */
    private function deleteMetaData(int $user_id): void
    {
        global $wpdb;
        $wpdb->delete($wpdb->usermeta, ['user_id' => $user_id]);
    }

    /**
     * Видаляє вподобані об'єкти користувача.
     */
    private function deleteFavorites(int $user_id): void
    {
        delete_user_meta($user_id, 'favorite_sights');
    }

    /**
     * Видаляє профільне фото користувача, якщо воно є.
     */
    private function deleteProfilePhoto(int $user_id): void
    {
        $photo_id = get_user_meta($user_id, 'profile_photo', true);

        if (!empty($photo_id)) {
            wp_delete_attachment((int)$photo_id, true);
            delete_user_meta($user_id, 'profile_photo');
        }
    }

    /**
     * Відписує користувача від розсилки.
     *
     * @param string $user_email
     * @throws Exception
     */

    private function unsubscribeUser(string $user_email): void
    {
        // Відписуємо користувача з Mailchimp
        MailchimpService::unsubscribe($user_email);
    }
}
