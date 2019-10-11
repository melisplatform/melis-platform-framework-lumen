-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2019 at 09:44 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.2.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `melis_demo_album`
--

CREATE TABLE `melis_demo_album` (
  `alb_id` int(11) NOT NULL,
  `alb_name` varchar(255) NOT NULL,
  `alb_date` date NOT NULL,
  `alb_song_num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `melis_demo_album`
--

INSERT INTO `melis_demo_album` (`alb_id`, `alb_name`, `alb_date`, `alb_song_num`) VALUES
(1, 'Judas', '2019-07-22', 1),
(2, 'Priest', '2019-07-17', 2),
(3, 'Parachute', '2019-07-22', 2),
(4, 'Green Eyes', '2019-07-23', 1),
(5, 'Mamaland', '2019-07-22', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `melis_demo_album`
--
ALTER TABLE `melis_demo_album`
  ADD PRIMARY KEY (`alb_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `melis_demo_album`
--
ALTER TABLE `melis_demo_album`
  MODIFY `alb_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
