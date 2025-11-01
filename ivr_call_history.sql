CREATE TABLE ivr_call_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NULL,
    trans_id BIGINT UNSIGNED NOT NULL,
    msisdn VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    call_received TINYINT(1) DEFAULT 0,
    duration INT DEFAULT 0,
    response_code VARCHAR(10) NULL,
    voice_duration INT DEFAULT 0,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_trans_id (trans_id),
    INDEX idx_msisdn (msisdn),
    INDEX idx_order_id (order_id)
);

