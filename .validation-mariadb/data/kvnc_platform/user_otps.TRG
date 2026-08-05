TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER tr_user_otps_sync_insert\nBEFORE INSERT ON user_otps\nFOR EACH ROW\nBEGIN\n  IF NEW.otp_hash IS NULL AND NEW.otp IS NOT NULL THEN\n    SET NEW.otp_hash = NEW.otp;\n  END IF;\n  IF NEW.otp IS NULL AND NEW.otp_hash IS NOT NULL THEN\n    SET NEW.otp = NEW.otp_hash;\n  END IF;\nEND' 'CREATE DEFINER=`root`@`localhost` TRIGGER tr_user_otps_sync_update\nBEFORE UPDATE ON user_otps\nFOR EACH ROW\nBEGIN\n  IF NEW.otp_hash IS NULL AND NEW.otp IS NOT NULL THEN\n    SET NEW.otp_hash = NEW.otp;\n  END IF;\n  IF NEW.otp IS NULL AND NEW.otp_hash IS NOT NULL THEN\n    SET NEW.otp = NEW.otp_hash;\n  END IF;\nEND'
sql_modes=1142947840 1142947840
definers='root@localhost' 'root@localhost'
client_cs_names='utf8mb4' 'utf8mb4'
connection_cl_names='utf8mb4_general_ci' 'utf8mb4_general_ci'
db_cl_names='latin1_swedish_ci' 'latin1_swedish_ci'
created=1785844013875358 1785844013883462
