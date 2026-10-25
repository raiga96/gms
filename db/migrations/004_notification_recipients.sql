-- Normalize CSV notification recipients while keeping legacy notifications.uid writers working.
CREATE TABLE IF NOT EXISTS notification_recipients (
    uid INT NOT NULL,
    notification_id INT NOT NULL,
    PRIMARY KEY (uid, notification_id),
    KEY idx_nr_notification (notification_id),
    CONSTRAINT fk_nr_user FOREIGN KEY (uid) REFERENCES users (uid) ON DELETE CASCADE,
    CONSTRAINT fk_nr_notification FOREIGN KEY (notification_id) REFERENCES notifications (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //
DROP TRIGGER IF EXISTS notifications_recipients_ai//
DROP TRIGGER IF EXISTS notifications_recipients_au//
DROP PROCEDURE IF EXISTS sync_notification_recipients//
CREATE PROCEDURE sync_notification_recipients(IN p_notification_id INT, IN p_uid_list VARCHAR(50))
BEGIN
    DECLARE v_remaining VARCHAR(50) DEFAULT '';
    DECLARE v_token VARCHAR(50) DEFAULT '';
    DECLARE v_comma INT DEFAULT 0;

    SET v_remaining = TRIM(COALESCE(p_uid_list, ''));
    WHILE v_remaining <> '' DO
        SET v_comma = LOCATE(',', v_remaining);
        IF v_comma > 0 THEN
            SET v_token = TRIM(SUBSTRING(v_remaining, 1, v_comma - 1));
            SET v_remaining = SUBSTRING(v_remaining, v_comma + 1);
        ELSE
            SET v_token = TRIM(v_remaining);
            SET v_remaining = '';
        END IF;

        IF v_token REGEXP '^[0-9]{1,10}$' AND CAST(v_token AS UNSIGNED) <= 2147483647 THEN
            INSERT IGNORE INTO notification_recipients (uid, notification_id)
            SELECT uid, p_notification_id FROM users WHERE uid = CAST(v_token AS UNSIGNED);
        END IF;
    END WHILE;
END//

CREATE TRIGGER notifications_recipients_ai AFTER INSERT ON notifications
FOR EACH ROW
BEGIN
    CALL sync_notification_recipients(NEW.id, NEW.uid);
END//

CREATE TRIGGER notifications_recipients_au AFTER UPDATE ON notifications
FOR EACH ROW
BEGIN
    DELETE FROM notification_recipients WHERE notification_id = OLD.id;
    CALL sync_notification_recipients(NEW.id, NEW.uid);
END//
DELIMITER ;

INSERT IGNORE INTO notification_recipients (uid, notification_id)
SELECT u.uid, n.id
FROM notifications n
JOIN (
    SELECT n.id, CAST(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(n.uid, ',', seq.n), ',', -1)) AS UNSIGNED) AS uid
    FROM notifications n
    JOIN (
        SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
        UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
        UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
        UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
        UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25
    ) seq ON seq.n <= 1 + LENGTH(n.uid) - LENGTH(REPLACE(n.uid, ',', ''))
    WHERE TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(n.uid, ',', seq.n), ',', -1)) REGEXP '^[0-9]{1,10}$'
) recipients ON recipients.id = n.id
JOIN users u ON u.uid = recipients.uid
WHERE recipients.uid <= 2147483647;
