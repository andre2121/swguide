CREATE TABLE IF NOT EXISTS `species` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `classification` varchar(20) NOT NULL,
  `designation` varchar(20) NOT NULL,
  `average_height` varchar(10) NOT NULL,
  `average_lifespan` varchar(10) NOT NULL,
  `eye_color` varchar(200) NOT NULL,
  `hair_color` varchar(200) NOT NULL,
  `skin_color` varchar(200) NOT NULL,
  `language` varchar(20) NOT NULL,
  `homeworld` varchar(5) NOT NULL,
  `people` varchar(200) NOT NULL,
  `films` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `species` ADD UNIQUE KEY `id` (`id`);