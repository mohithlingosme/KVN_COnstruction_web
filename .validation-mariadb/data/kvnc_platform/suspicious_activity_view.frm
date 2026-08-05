TYPE=VIEW
query=select `sa`.`id` AS `id`,`sa`.`user_id` AS `user_id`,`sa`.`ip_address` AS `ip_address`,`sa`.`activity_type` AS `activity_type`,`sa`.`severity` AS `severity`,`sa`.`details` AS `details`,`sa`.`resolved` AS `resolved`,`sa`.`resolved_at` AS `resolved_at`,`sa`.`resolved_by` AS `resolved_by`,`sa`.`created_at` AS `created_at`,`sa`.`updated_at` AS `updated_at`,`sa`.`deleted_at` AS `deleted_at`,`u`.`full_name` AS `user_name`,`u`.`email` AS `user_email` from (`kvnc_platform`.`suspicious_activity` `sa` left join `kvnc_platform`.`users` `u` on(`u`.`id` = `sa`.`user_id`)) where `sa`.`resolved` = 0 and `sa`.`created_at` > current_timestamp() - interval 7 day
md5=55283dedd5b60067b272e57801d5d513
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001785844015546690
create-version=2
source=SELECT sa.*, u.full_name AS user_name, u.email AS user_email\n  FROM suspicious_activity sa\n  LEFT JOIN users u ON u.id = sa.user_id\n  WHERE sa.resolved = 0 AND sa.created_at > CURRENT_TIMESTAMP - INTERVAL 7 DAY
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `sa`.`id` AS `id`,`sa`.`user_id` AS `user_id`,`sa`.`ip_address` AS `ip_address`,`sa`.`activity_type` AS `activity_type`,`sa`.`severity` AS `severity`,`sa`.`details` AS `details`,`sa`.`resolved` AS `resolved`,`sa`.`resolved_at` AS `resolved_at`,`sa`.`resolved_by` AS `resolved_by`,`sa`.`created_at` AS `created_at`,`sa`.`updated_at` AS `updated_at`,`sa`.`deleted_at` AS `deleted_at`,`u`.`full_name` AS `user_name`,`u`.`email` AS `user_email` from (`kvnc_platform`.`suspicious_activity` `sa` left join `kvnc_platform`.`users` `u` on(`u`.`id` = `sa`.`user_id`)) where `sa`.`resolved` = 0 and `sa`.`created_at` > current_timestamp() - interval 7 day
mariadb-version=100432
