CREATE TABLE IF NOT EXISTS `films` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `episode_id` int(1) NOT NULL,
  `opening_crawl` text NOT NULL,
  `director` varchar(50) NOT NULL,
  `producer` varchar(50) NOT NULL,
  `release_date` varchar(11) NOT NULL,
  `species` varchar(100) NOT NULL,
  `starships` varchar(100) NOT NULL,
  `vehicles` varchar(100) NOT NULL,
  `characters` varchar(200) NOT NULL,
  `planets` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `films` ADD UNIQUE KEY `id` (`id`);
