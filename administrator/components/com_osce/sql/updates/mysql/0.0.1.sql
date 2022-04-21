DROP TABLE IF EXISTS `#__osce_quesbanks`;
DROP TABLE IF EXISTS `#__osce_tags`;
DROP TABLE IF EXISTS `#__osce_quesbank_tags`;
DROP TABLE IF EXISTS `#__osce_fileinfo`;

CREATE TABLE `#__osce_fileinfo` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `#__osce_quesbanks`
--

CREATE TABLE `#__osce_quesbanks` (
  `id` int(11) NOT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '1',
  `ques` text,
  `opt1` text,
  `opt2` text,
  `opt3` text,
  `opt4` text,
  `tag_id` json DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11),
  `correct_ans` text,
  `modified_on` datetime NOT NULL 
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `#__osce_quesbank_tags`
--

CREATE TABLE `#__osce_quesbank_tags` (
  `id` int(11) NOT NULL,
  `quesbank_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `#__osce_tags`
--

CREATE TABLE `#__osce_tags` (
  `id` int(11) NOT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '1',
  `title` text NOT NULL,
  `created_by` varchar(255) NOT NULL DEFAULT 'user',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
  `modified_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#__osce_fileinfo`
--
ALTER TABLE `#__osce_fileinfo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#__osce_quesbanks`
--
ALTER TABLE `#__osce_quesbanks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#__osce_quesbank_tags`
--
ALTER TABLE `#__osce_quesbank_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#__osce_tags`
--
ALTER TABLE `#__osce_tags`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `#__osce_fileinfo`
--
ALTER TABLE `#__osce_fileinfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `#__osce_quesbanks`
--
ALTER TABLE `#__osce_quesbanks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `#__osce_quesbank_tags`
--
ALTER TABLE `#__osce_quesbank_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `#__osce_tags`
--
ALTER TABLE `#__osce_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
