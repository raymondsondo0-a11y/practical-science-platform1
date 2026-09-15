CREATE TABLE IF NOT EXISTS trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  destination VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  departure_at DATETIME NOT NULL,
  capacity INT UNSIGNED NOT NULL DEFAULT 12,
  organizer_name VARCHAR(120) NOT NULL,
  organizer_phone VARCHAR(40) NOT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  status ENUM('published','cancelled','completed') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_departure (departure_at),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS participants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(190) NOT NULL,
  people INT UNSIGNED NOT NULL DEFAULT 1,
  message TEXT DEFAULT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_trip_email (trip_id,email),
  CONSTRAINT fk_participant_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
  INDEX idx_trip (trip_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
