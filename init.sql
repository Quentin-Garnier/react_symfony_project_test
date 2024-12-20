CREATE TABLE IF NOT EXISTS `test` (
    `id_leadflow` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(255) NOT NULL,
    `prenom` VARCHAR(255) NOT NULL,
    `id` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `telephone` VARCHAR(20) NOT NULL,
    `date_transmission` DATETIME NOT NULL,
    `retour_LF` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_leadflow`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO `test` (`nom`, `prenom`, `id`, `email`, `telephone`, `date_transmission`, `retour_LF`)
VALUES 
('Bernard', 'Lucas', SUBSTRING(MD5(RAND()), 1, 12), 'lucas.bernard@example.com', '0608091011', '2010-01-01 10:00:00', 'NOK'),
('Dupont', 'Marie', SUBSTRING(MD5(RAND()), 1, 12), 'marie.dupont@example.com', '0608091012', '2011-02-15 11:00:00', 'OK'),
('Martin', 'Paul', SUBSTRING(MD5(RAND()), 1, 12), 'paul.martin@example.com', '0608091013', '2011-03-20 12:00:00', 'DBL'), -- Date rapprochée
('Leroy', 'Sophie', SUBSTRING(MD5(RAND()), 1, 12), 'sophie.leroy@example.com', '0608091014', '2013-04-25 13:00:00', 'NOK'),
('Durand', 'Thomas', SUBSTRING(MD5(RAND()), 1, 12), 'thomas.durand@example.com', '0608091015', '2014-05-30 14:00:00', 'OK'),
('Petit', 'Chloé', SUBSTRING(MD5(RAND()), 1, 12), 'chloe.petit@example.com', '0608091016', '2014-06-05 15:00:00', 'DBL'), -- Date rapprochée
('Garnier', 'Lucas', SUBSTRING(MD5(RAND()), 1, 12), 'lucas.garnier@example.com', '0608091017', '2015-07-10 16:00:00', 'NOK'),
('Moreau', 'Emma', SUBSTRING(MD5(RAND()), 1, 12), 'emma.moreau@example.com', '0608091018', '2016-08-15 17:00:00', 'OK'),
('Lemoine', 'Julien', SUBSTRING(MD5(RAND()), 1, 12), 'julien.lemoine@example.com', '0608091019', '2018-09-20 18:00:00', 'DBL'),
('Blanc', 'Claire', SUBSTRING(MD5(RAND()), 1, 12), 'claire.blanc@example.com', '0608091020', '2019-10-25 19:00:00', 'NOK'),
('Simon', 'Alexandre', SUBSTRING(MD5(RAND()), 1, 12), 'alexandre.simon@example.com', '0608091021', '2020-11-30 20:00:00', 'OK'),
('Fournier', 'Laura', SUBSTRING(MD5(RAND()), 1, 12), 'laura.fournier@example.com', '0608091022', '2021-12-05 21:00:00', 'DBL'),
('Rousseau', 'Louis', SUBSTRING(MD5(RAND()), 1, 12), 'louis.rousseau@example.com', '0608091023', '2022-01-10 22:00:00', 'NOK'),
('Gauthier', 'Camille', SUBSTRING(MD5(RAND()), 1, 12), 'camille.gauthier@example.com', '0608091024', '2023-02-15 23:00:00', 'OK'),
('Pires', 'Alice', SUBSTRING(MD5(RAND()), 1, 12), 'alice.pires@example.com', '0608091025', '2023-03-20 00:00:00', 'DBL'), -- Date rapprochée
('Lemoine', 'Victor', SUBSTRING(MD5(RAND()), 1, 12), 'victor.lemoine@example.com', '0608091026', '2023-04-25 01:00:00', 'NOK'),
('Gonzalez', 'Mélanie', SUBSTRING(MD5(RAND()), 1, 12), 'melanie.gonzalez@example.com', '0608091027', '2023-05-30 02:00:00', 'OK'),
('Bouchard', 'Lucas', SUBSTRING(MD5(RAND()), 1, 12), 'lucas.bouchard@example.com', '0608091028', '2024-06-04 03:00:00', 'DBL'),
('Marin', 'Juliette', SUBSTRING(MD5(RAND()), 1, 12), 'juliette.marin@example.com', '0608091029', '2024-07-10 04:00:00', 'NOK'),
('Chevalier', 'Hugo', SUBSTRING(MD5(RAND()), 1, 12), 'hugo.chevalier@example.com', '0608091030', '2024-08-15 05:00:00', 'OK');







CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `prenom` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `assigned_tasks` JSON DEFAULT NULL,
    `is_admin` BOOLEAN DEFAULT FALSE,
    `super_admin` BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO `users` (`nom`, `prenom`, `email`, `password`, `assigned_tasks`, `is_admin`) VALUES
('Dupont', 'Jean', 'jean.dupont@example.com', 'password123', NULL, TRUE, TRUE),
('Martin', 'Sophie', 'sophie.martin@example.com', 'password456', NULL, TRUE, FALSE),
('Durand', 'Pierre', 'pierre.durand@example.com', 'password789', NULL, FALSE, FALSE),
('Leroy', 'Lucie', 'lucie.leroy@example.com', 'password012', NULL, FALSE, FALSE),
('Moreau', 'Clément', 'clement.moreau@example.com', 'password345', NULL, FALSE, FALSE);





CREATE TABLE IF NOT EXISTS `tasks` (
    `task_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `assigned_users` JSON DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




INSERT INTO `tasks`(`nom`, `description`, `assigned_users`) VALUES
('Design Homepage', 'Create the layout for the new homepage including navigation and footer.', NULL),
('Implement User Authentication maybe', 'Develop user registration and login features using JWT.', NULL),
('Set Up Database', 'Configure the MySQL database and set up the initial schema.', JSON_ARRAY(2, 4)),
('Write API Documentation', 'Document the REST API endpoints and usage instructions.', JSON_ARRAY(1)),
('Conduct User Testing', 'Perform usability testing with a group of selected users and gather feedback.', JSON_ARRAY(1, 4)),
('Tâche 2', 'Description de la tâche 2', NULL),
('Tâche 3', 'Description de la tâche 3', JSON_ARRAY(3)),
('Tâche 1', 'Description de la tâche 1', JSON_ARRAY(2)),
('tets csv', 'fichier de test csv', JSON_ARRAY(4, 1));







CREATE TABLE IF NOT EXISTS `responses` (
    `response_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `assigned_task` JSON NOT NULL,
    `responding_user` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO `responses`(`nom`, `description`, `assigned_task`, `responding_user`) VALUES
('test csv', 'Fichier de test csv', 3, 2),
('truc', 'blablabla', 3, 2),
('Réunion', 'Préparer la présentation pour la réunion de demain', 3, 3),
('Plan', 'Plan du test', 8, 2);





CREATE TABLE IF NOT EXISTS `table_collecte` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_annonceur` INT NOT NULL,
    `prénom` VARCHAR(150) NULL,
    `nom` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `adresse` VARCHAR(250) NOT NULL,
    `date_naissance` VARCHAR(10) NOT NULL,
    `code_postal` VARCHAR(5) NOT NULL,
    `ville` VARCHAR(255) NOT NULL,
    `téléphone` VARCHAR(10) NOT NULL,
    `autres` TEXT NOT NULL,
    `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;