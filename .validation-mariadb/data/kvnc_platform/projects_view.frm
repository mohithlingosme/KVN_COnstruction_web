TYPE=VIEW
query=select `p`.`id` AS `id`,`p`.`project_code` AS `project_code`,`p`.`title` AS `title`,`p`.`name` AS `name`,`p`.`project_name` AS `project_name`,`p`.`project_type` AS `project_type`,`p`.`slug` AS `slug`,`p`.`client_id` AS `client_id`,`p`.`client_user_id` AS `client_user_id`,`p`.`lead_id` AS `lead_id`,`p`.`quotation_id` AS `quotation_id`,`p`.`status_id` AS `status_id`,`p`.`location` AS `location`,`p`.`description` AS `description`,`p`.`budget` AS `budget`,`p`.`progress` AS `progress`,`p`.`start_date` AS `start_date`,`p`.`end_date` AS `end_date`,`p`.`project_image` AS `project_image`,`p`.`created_by` AS `created_by`,`p`.`updated_by` AS `updated_by`,`p`.`status` AS `status`,`p`.`created_at` AS `created_at`,`p`.`updated_at` AS `updated_at`,`p`.`deleted_at` AS `deleted_at`,`u`.`full_name` AS `client_name`,`u`.`email` AS `client_email`,`u`.`phone` AS `client_phone` from (`kvnc_platform`.`projects` `p` left join `kvnc_platform`.`users` `u` on(`u`.`id` = `p`.`client_id`))
md5=bed69a32676b78c80e2c4c6ee7bb0f28
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001785844015558957
create-version=2
source=SELECT p.*, u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone\n  FROM projects p\n  LEFT JOIN users u ON u.id = p.client_id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `p`.`id` AS `id`,`p`.`project_code` AS `project_code`,`p`.`title` AS `title`,`p`.`name` AS `name`,`p`.`project_name` AS `project_name`,`p`.`project_type` AS `project_type`,`p`.`slug` AS `slug`,`p`.`client_id` AS `client_id`,`p`.`client_user_id` AS `client_user_id`,`p`.`lead_id` AS `lead_id`,`p`.`quotation_id` AS `quotation_id`,`p`.`status_id` AS `status_id`,`p`.`location` AS `location`,`p`.`description` AS `description`,`p`.`budget` AS `budget`,`p`.`progress` AS `progress`,`p`.`start_date` AS `start_date`,`p`.`end_date` AS `end_date`,`p`.`project_image` AS `project_image`,`p`.`created_by` AS `created_by`,`p`.`updated_by` AS `updated_by`,`p`.`status` AS `status`,`p`.`created_at` AS `created_at`,`p`.`updated_at` AS `updated_at`,`p`.`deleted_at` AS `deleted_at`,`u`.`full_name` AS `client_name`,`u`.`email` AS `client_email`,`u`.`phone` AS `client_phone` from (`kvnc_platform`.`projects` `p` left join `kvnc_platform`.`users` `u` on(`u`.`id` = `p`.`client_id`))
mariadb-version=100432
