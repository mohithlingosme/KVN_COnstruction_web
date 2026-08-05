TYPE=VIEW
query=select count(case when `kvnc_platform`.`security_logs`.`severity` = \'critical\' then 1 end) AS `critical_events`,count(case when `kvnc_platform`.`security_logs`.`severity` = \'warning\' then 1 end) AS `warning_events`,count(case when `kvnc_platform`.`security_logs`.`severity` = \'info\' then 1 end) AS `info_events`,count(case when `kvnc_platform`.`security_logs`.`created_at` > current_timestamp() - interval 24 hour then 1 end) AS `events_today` from `kvnc_platform`.`security_logs` where `kvnc_platform`.`security_logs`.`created_at` > current_timestamp() - interval 7 day
md5=f435ddb34bd97b807180b94cc4a29750
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001785844015543380
create-version=2
source=SELECT\n    COUNT(CASE WHEN severity = \'critical\' THEN 1 END) AS critical_events,\n    COUNT(CASE WHEN severity = \'warning\' THEN 1 END) AS warning_events,\n    COUNT(CASE WHEN severity = \'info\' THEN 1 END) AS info_events,\n    COUNT(CASE WHEN created_at > CURRENT_TIMESTAMP - INTERVAL 24 HOUR THEN 1 END) AS events_today\n  FROM security_logs\n  WHERE created_at > CURRENT_TIMESTAMP - INTERVAL 7 DAY
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select count(case when `kvnc_platform`.`security_logs`.`severity` = \'critical\' then 1 end) AS `critical_events`,count(case when `kvnc_platform`.`security_logs`.`severity` = \'warning\' then 1 end) AS `warning_events`,count(case when `kvnc_platform`.`security_logs`.`severity` = \'info\' then 1 end) AS `info_events`,count(case when `kvnc_platform`.`security_logs`.`created_at` > current_timestamp() - interval 24 hour then 1 end) AS `events_today` from `kvnc_platform`.`security_logs` where `kvnc_platform`.`security_logs`.`created_at` > current_timestamp() - interval 7 day
mariadb-version=100432
