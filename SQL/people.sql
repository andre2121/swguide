CREATE TABLE IF NOT EXISTS `people` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `birth_year` varchar(10) NOT NULL,
  `eye_color` varchar(20) NOT NULL,
  `gender` varchar(15) NOT NULL,
  `hair_color` varchar(20) NOT NULL,
  `height` varchar(20) NOT NULL,
  `mass` varchar(20) NOT NULL,
  `skin_color` varchar(20) NOT NULL,
  `homeworld` varchar(5) NOT NULL,
  `films` varchar(30) NOT NULL,
  `species` varchar(50) NOT NULL,
  `starships` varchar(100) NOT NULL,
  `vehicles` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `people` ADD UNIQUE KEY `id` (`id`);