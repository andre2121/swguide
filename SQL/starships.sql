CREATE TABLE IF NOT EXISTS `starships` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `starship_class` varchar(50) NOT NULL,
  `manufacturer` varchar(200) NOT NULL,
  `cost_in_credits` varchar(20) NOT NULL,
  `length` varchar(10) NOT NULL,
  `crew` varchar(10) NOT NULL,
  `passengers` varchar(10) NOT NULL,
  `max_atmosphering_speed` varchar(20) NOT NULL,
  `hyperdrive_rating` varchar(20) NOT NULL,
  `MGLT` varchar(20) NOT NULL,
  `cargo_capacity` varchar(20) NOT NULL,
  `consumables` varchar(20) NOT NULL,
  `films` varchar(30) NOT NULL,
  `pilots` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `people` ADD UNIQUE KEY `id` (`id`);