TYPE=VIEW
query=select `kvnc_platform`.`login_attempts`.`ip_address` AS `ip_address`,`kvnc_platform`.`login_attempts`.`email` AS `email`,count(0) AS `attempt_count`,max(`kvnc_platform`.`login_attempts`.`created_at`) AS `last_attempt` from `kvnc_platform`.`login_attempts` where `kvnc_platform`.`login_attempts`.`success` = 0 and `kvnc_platform`.`login_attempts`.`created_at` > current_timestamp() - interval 24 hour group by `kvnc_platform`.`login_attempts`.`ip_address`,`kvnc_platform`.`login_attempts`.`email` having count(0) >= 3
md5=37f834a9e48add8588b792b50600e34f
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001785844015539599
create-version=2
source=SELECT ip_address, email, COUNT(*) AS attempt_count, MAX(created_at) AS last_attempt\n  FROM login_attempts\n  WHERE success = 0 AND created_at > CURRENT_TIMESTAMP - INTERVAL 24 HOUR\n  GROUP BY ip_address, email\n  HAVING COUNT(*) >= 3
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `kvnc_platform`.`login_attempts`.`ip_address` AS `ip_address`,`kvnc_platform`.`login_attempts`.`email` AS `email`,count(0) AS `attempt_count`,max(`kvnc_platform`.`login_attempts`.`created_at`) AS `last_attempt` from `kvnc_platform`.`login_attempts` where `kvnc_platform`.`login_attempts`.`success` = 0 and `kvnc_platform`.`login_attempts`.`created_at` > current_timestamp() - interval 24 hour group by `kvnc_platform`.`login_attempts`.`ip_address`,`kvnc_platform`.`login_attempts`.`email` having count(0) >= 3
mariadb-version=100432
