CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_type ENUM('wallet', 'bank_account') NOT NULL,
    account_address VARCHAR(255) UNIQUE NOT NULL,
    bank_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE projects (
    project_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    goal_amount DECIMAL(15, 2) NOT NULL,
    raised_amount DECIMAL(15, 2) DEFAULT 0,
    creator_user_id INT NOT NULL,
    status ENUM('active', 'completed', 'paused', 'canceled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_user_id) REFERENCES users(user_id)
);
CREATE TABLE campaigns (
    campaign_id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    target_amount DECIMAL(15, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'paused', 'canceled') DEFAULT 'active',
    FOREIGN KEY (project_id) REFERENCES projects(project_id)
);
CREATE TABLE pledges (
    pledge_id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE fund_release_conditions (
    condition_id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    release_type ENUM('milestone', 'amount_reached') NOT NULL,
    milestone_description VARCHAR(255),
    release_amount DECIMAL(15, 2),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id)
);
CREATE TABLE disbursements (
    disbursement_id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    disbursed_to_account_id INT NOT NULL,
    condition_id INT NOT NULL,
    disbursement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id),
    FOREIGN KEY (disbursed_to_account_id) REFERENCES accounts(account_id),
    FOREIGN KEY (condition_id) REFERENCES fund_release_conditions(condition_id)
);
