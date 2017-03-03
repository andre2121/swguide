CREATE TABLE IF NOT EXISTS `planets` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `diameter` varchar(20) NOT NULL,
  `rotation_period` varchar(20) NOT NULL,
  `orbital_period` varchar(20) NOT NULL,
  `gravity` varchar(20) NOT NULL,
  `population` varchar(20) NOT NULL,
  `climate` varchar(200) NOT NULL,
  `terrain` varchar(200) NOT NULL,
  `surface_water` varchar(200) NOT NULL,
  `residents` varchar(200) NOT NULL,
  `films` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `people` ADD UNIQUE KEY `id` (`id`);