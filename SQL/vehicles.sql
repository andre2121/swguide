CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `vehicle_class` varchar(50) NOT NULL,
  `manufacturer` varchar(200) NOT NULL,
  `length` varchar(10) NOT NULL,
  `cost_in_credits` varchar(10) NOT NULL,
  `crew` varchar(10) NOT NULL,
  `passengers` varchar(10) NOT NULL,
  `max_atmosphering_speed` varchar(20) NOT NULL,
  `cargo_capacity` varchar(10) NOT NULL,
  `consumables` varchar(50) NOT NULL,
  `films` varchar(30) NOT NULL,
  `pilots` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `people` ADD UNIQUE KEY `id` (`id`);