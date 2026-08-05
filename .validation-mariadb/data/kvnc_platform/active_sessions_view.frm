TYPE=VIEW
query=select `us`.`id` AS `id`,`us`.`user_id` AS `user_id`,`us`.`session_token` AS `session_token`,`us`.`ip_address` AS `ip_address`,`us`.`user_agent` AS `user_agent`,`us`.`last_activity` AS `last_activity`,`us`.`created_at` AS `session_started`,`us`.`expires_at` AS `expires_at`,`u`.`full_name` AS `full_name`,`u`.`email` AS `email` from (`kvnc_platform`.`user_sessions` `us` left join `kvnc_platform`.`users` `u` on(`u`.`id` = `us`.`user_id`)) where `us`.`is_active` = 1 and `us`.`revoked_at` is null
md5=df00767c9ea6c633bfe81db6383b3178
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001785844015535497
create-version=2
source=SELECT us.id, us.user_id, us.session_token, us.ip_address, us.user_agent, us.last_activity, us.created_at AS session_started, us.expires_at, u.full_name, u.email\n  FROM user_sessions us\n  LEFT JOIN users u ON u.id = us.user_id\n  WHERE us.is_active = 1 AND us.revoked_at IS NULL
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `us`.`id` AS `id`,`us`.`user_id` AS `user_id`,`us`.`session_token` AS `session_token`,`us`.`ip_address` AS `ip_address`,`us`.`user_agent` AS `user_agent`,`us`.`last_activity` AS `last_activity`,`us`.`created_at` AS `session_started`,`us`.`expires_at` AS `expires_at`,`u`.`full_name` AS `full_name`,`u`.`email` AS `email` from (`kvnc_platform`.`user_sessions` `us` left join `kvnc_platform`.`users` `u` on(`u`.`id` = `us`.`user_id`)) where `us`.`is_active` = 1 and `us`.`revoked_at` is null
mariadb-version=100432
