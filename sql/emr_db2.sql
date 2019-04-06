-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2018 at 08:34 PM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emr_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aa_consults`
--

CREATE TABLE `aa_consults` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime DEFAULT NULL,
  `patient_id` varchar(36) NOT NULL,
  `medical_group` varchar(40) DEFAULT NULL,
  `chief_physician` varchar(40) DEFAULT NULL,
  `signing_physician` varchar(40) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `notes` text,
  `status` tinyint(1) UNSIGNED DEFAULT NULL,
  `datetime_started` datetime NOT NULL,
  `datetime_completed` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE `admin_user` (
  `id` varchar(36) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chief_complaints`
--

CREATE TABLE `chief_complaints` (
  `id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `selected_value` tinyint(3) UNSIGNED DEFAULT NULL,
  `custom_text` varchar(40) DEFAULT NULL,
  `type` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `name` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses_conditions_illnesses`
--

CREATE TABLE `diagnoses_conditions_illnesses` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime DEFAULT NULL,
  `consult_id` varchar(36) DEFAULT NULL,
  `patient_id` varchar(36) NOT NULL,
  `is_chronic` tinyint(3) UNSIGNED DEFAULT NULL,
  `category` tinyint(3) UNSIGNED DEFAULT NULL,
  `type` tinyint(3) UNSIGNED DEFAULT NULL,
  `other` varchar(30) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text,
  `datetime_created` datetime DEFAULT NULL,
  `history_show` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `is_normal` tinyint(1) UNSIGNED DEFAULT NULL,
  `main_category` tinyint(3) UNSIGNED DEFAULT NULL,
  `arg1` tinyint(3) UNSIGNED DEFAULT NULL,
  `arg2` tinyint(3) UNSIGNED DEFAULT NULL,
  `arg3` tinyint(3) UNSIGNED DEFAULT NULL,
  `arg4` tinyint(3) UNSIGNED DEFAULT NULL,
  `information` varchar(30) DEFAULT NULL,
  `options` varchar(20) DEFAULT NULL,
  `other_option` varchar(30) DEFAULT NULL,
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `followups`
--

CREATE TABLE `followups` (
  `id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `is_needed` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_type_custom` tinyint(1) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `is_reason_custom` tinyint(1) UNSIGNED DEFAULT NULL,
  `reason` varchar(30) DEFAULT NULL,
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `history_allergies`
--

CREATE TABLE `history_allergies` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime DEFAULT NULL,
  `patient_id` varchar(36) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `notes` text,
  `datetime_created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `history_medications`
--

CREATE TABLE `history_medications` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime DEFAULT NULL,
  `consult_id` varchar(36) DEFAULT NULL,
  `patient_id` varchar(36) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `source` tinyint(4) DEFAULT NULL,
  `notes` text,
  `datetime_created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `history_surgeries`
--

CREATE TABLE `history_surgeries` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime DEFAULT NULL,
  `patient_id` varchar(36) NOT NULL,
  `is_name_custom` tinyint(1) UNSIGNED DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `notes` text,
  `datetime_created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hpi_general`
--

CREATE TABLE `hpi_general` (
  `chief_complaint_id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `o_how` varchar(20) DEFAULT NULL,
  `o_cause` varchar(20) DEFAULT NULL,
  `p_provocation` varchar(20) DEFAULT NULL,
  `p_palliation` varchar(20) DEFAULT NULL,
  `q_type` varchar(20) DEFAULT NULL,
  `r_region_main` varchar(20) DEFAULT NULL,
  `r_region_radiates` varchar(20) DEFAULT NULL,
  `s_level` tinyint(3) UNSIGNED DEFAULT NULL,
  `t_begin_time` varchar(4) DEFAULT NULL,
  `t_before` tinyint(1) UNSIGNED DEFAULT NULL,
  `t_current` tinyint(1) UNSIGNED DEFAULT NULL,
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hpi_pregnancy`
--

CREATE TABLE `hpi_pregnancy` (
  `chief_complaint_id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `num_weeks_pregnant` tinyint(3) UNSIGNED DEFAULT NULL,
  `receiving_prenatal_care` tinyint(1) UNSIGNED DEFAULT NULL,
  `taking_prenatal_vitamins` tinyint(1) UNSIGNED DEFAULT NULL,
  `received_ultrasound` tinyint(1) UNSIGNED DEFAULT NULL,
  `num_live_births` tinyint(3) UNSIGNED DEFAULT NULL,
  `num_miscarriages` tinyint(3) UNSIGNED DEFAULT NULL,
  `dysuria_urgency_frequency` tinyint(1) UNSIGNED DEFAULT NULL,
  `abnormal_vaginal_discharge` tinyint(1) UNSIGNED DEFAULT NULL,
  `vaginal_bleeding` tinyint(1) UNSIGNED DEFAULT NULL,
  `previous_pregnancy_complications` tinyint(1) UNSIGNED DEFAULT NULL,
  `complications_notes` text,
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `measurements`
--

CREATE TABLE `measurements` (
  `id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `is_pregnant` tinyint(1) UNSIGNED DEFAULT NULL,
  `date_last_menstruation` date DEFAULT NULL,
  `temperature_units` tinyint(1) UNSIGNED DEFAULT NULL,
  `temperature_value` float DEFAULT NULL,
  `blood_pressure_systolic` tinyint(3) UNSIGNED DEFAULT NULL,
  `blood_pressure_diastolic` tinyint(3) UNSIGNED DEFAULT NULL,
  `pulse_rate` tinyint(3) UNSIGNED DEFAULT NULL,
  `blood_oxygen_saturation` float DEFAULT NULL,
  `respiration_rate` tinyint(3) UNSIGNED DEFAULT NULL,
  `height_units` tinyint(1) UNSIGNED DEFAULT NULL,
  `height_value` float DEFAULT NULL,
  `weight_units` tinyint(1) UNSIGNED DEFAULT NULL,
  `weight_value` float DEFAULT NULL,
  `waist_circumference_units` tinyint(1) UNSIGNED DEFAULT NULL,
  `waist_circumference_value` float DEFAULT NULL,
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime NOT NULL,
  `patient_id` varchar(36) NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `submitter` varchar(30) NOT NULL,
  `datetime_created` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` varchar(36) NOT NULL,
  `datetime_last_updated` datetime NOT NULL,
  `name` varchar(50) NOT NULL,
  `community_name` varchar(30) NOT NULL,
  `sex` tinyint(1) NOT NULL,
  `exact_date_of_birth_known` tinyint(1) NOT NULL,
  `date_of_birth` date NOT NULL,
  `datetime_registered` datetime NOT NULL,
  `consult_status` tinyint(1) DEFAULT NULL,
  `consult_status_datetime` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `default_consult_location` varchar(30) DEFAULT NULL,
  `default_consult_medical_group` varchar(40) DEFAULT NULL,
  `default_consult_chief_physician` varchar(40) DEFAULT NULL,
  `edit_code` varchar(30) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `id` varchar(36) NOT NULL,
  `consult_id` varchar(36) NOT NULL,
  `diagnosis_id` varchar(36) DEFAULT NULL,
  `type` tinyint(3) UNSIGNED DEFAULT NULL,
  `other` varchar(30) DEFAULT NULL,
  `strength` smallint(5) UNSIGNED DEFAULT NULL,
  `strength_units` tinyint(3) UNSIGNED DEFAULT NULL,
  `strength_units_other` varchar(30) DEFAULT NULL,
  `conc_part_one` smallint(5) UNSIGNED DEFAULT NULL,
  `conc_part_one_units` tinyint(3) UNSIGNED DEFAULT NULL,
  `conc_part_one_units_other` varchar(30) DEFAULT NULL,
  `conc_part_two` smallint(5) UNSIGNED DEFAULT NULL,
  `conc_part_two_units` tinyint(3) UNSIGNED DEFAULT NULL,
  `conc_part_two_units_other` varchar(30) DEFAULT NULL,
  `quantity` smallint(5) UNSIGNED DEFAULT NULL,
  `quantity_units` tinyint(3) UNSIGNED DEFAULT NULL,
  `quantity_units_other` varchar(30) DEFAULT NULL,
  `route` tinyint(3) UNSIGNED DEFAULT NULL,
  `route_other` varchar(30) DEFAULT NULL,
  `prn` tinyint(1) UNSIGNED DEFAULT NULL,
  `dosage` smallint(5) UNSIGNED DEFAULT NULL,
  `dosage_units` tinyint(3) UNSIGNED DEFAULT NULL,
  `dosage_units_other` varchar(30) DEFAULT NULL,
  `frequency` tinyint(3) UNSIGNED DEFAULT NULL,
  `frequency_other` varchar(30) DEFAULT NULL,
  `duration` smallint(5) UNSIGNED DEFAULT NULL,
  `duration_units` tinyint(3) UNSIGNED DEFAULT NULL,
  `duration_units_other` varchar(30) DEFAULT NULL,
  `notes` text,
  `add_to_medication_history` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aa_consults`
--
ALTER TABLE `aa_consults`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chief_complaints`
--
ALTER TABLE `chief_complaints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `diagnoses_conditions_illnesses`
--
ALTER TABLE `diagnoses_conditions_illnesses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `followups`
--
ALTER TABLE `followups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history_allergies`
--
ALTER TABLE `history_allergies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history_medications`
--
ALTER TABLE `history_medications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history_surgeries`
--
ALTER TABLE `history_surgeries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hpi_general`
--
ALTER TABLE `hpi_general`
  ADD PRIMARY KEY (`chief_complaint_id`);

--
-- Indexes for table `hpi_pregnancy`
--
ALTER TABLE `hpi_pregnancy`
  ADD PRIMARY KEY (`chief_complaint_id`);

--
-- Indexes for table `measurements`
--
ALTER TABLE `measurements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
