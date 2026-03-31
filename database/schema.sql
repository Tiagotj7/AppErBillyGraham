CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','conselheiro') NOT NULL DEFAULT 'conselheiro',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE people (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  birthdate DATE NOT NULL,
  role ENUM('escudeiro','arauto','sênior','emérito','conselheiro') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attendance_date DATE NOT NULL,
  person_id INT NOT NULL,
  status ENUM('present','absent') NOT NULL DEFAULT 'absent',
  recorded_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_date_person (attendance_date, person_id),
  CONSTRAINT fk_att_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
  CONSTRAINT fk_att_user FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE users SET role='admin' WHERE email='SEU_EMAIL@EMAIL.COM';

CREATE TABLE attendance_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attendance_date DATE NOT NULL,
  user_id INT NULL,
  action ENUM('save') NOT NULL DEFAULT 'save',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attlog_date (attendance_date),
  CONSTRAINT fk_attlog_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attendance_log_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  log_id INT NOT NULL,
  person_id INT NOT NULL,
  old_status ENUM('present','absent') NULL,
  new_status ENUM('present','absent') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_logitem_log (log_id),
  INDEX idx_logitem_person (person_id),

  CONSTRAINT fk_logitem_log FOREIGN KEY (log_id) REFERENCES attendance_logs(id) ON DELETE CASCADE,
  CONSTRAINT fk_logitem_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_people_birthdate ON people(birthdate);
CREATE INDEX idx_att_person_status ON attendance(person_id, status);