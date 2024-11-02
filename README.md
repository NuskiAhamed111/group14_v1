# UoJ_IDMS - Industrial Diary Management System for DCS_UoJ

## 1. Introduction
Welcome to the Industrial Diary Management System for University Departments. This system helps manage and document the weekly industrial activities, inspection reports, and progress reports of students involved in industrial training or internships. This README provides an overview of the system, its features, and instructions for installation and usage.

## 2. Features
### User Authentication
- Secure login and access control for administrators and users.
  
### Weekly Activity Logging
- Diary Entry: Students can record their weekly activities, including tasks performed, observations, and any insights gained during their industrial training.
- Staff Inspection Reports: Staff members can upload inspection reports documenting evaluations of student performance.

### Activity Review & Management
- Mentor Review: Mentors can review, comment on, and provide feedback on students’ weekly logs and overallprocessreports.
- Staff Review: Staff can review inspection Reports.
- Progress Tracking: Administrators and instructors can track students’ progress and overall engagement through logged entries.

### Printable Document Generation
Export and Print: Generate and print documents such as monthly prcess reports,overall process reports, and inspection reports for record-keeping and assessment purposes.

### Performance Evaluation and Final Result Calculation
- Marks Assignment: Assign marks to students based on mentor feedback and performance throughout the training period.
- Final Result Calculation: Calculate the final result for each student, incorporating weekly logs, inspection reports,overal process reports and mentor feedback and staff feedback for a comprehensive evaluation of the industrial training.

## 3. Installation
To install and set up the Attendance Management System, follow these steps:

### Requirements
- Web server (e.g., Apache, Nginx)
- PHP (>= 7.0) and a MySQL database
- Composer for PHP

#### Clone the Repository in to desired server path
```bash
git clone  
```
#### change directory in to UoJ_AMS
```bash
cd UoJ_IDMS
```
#### configure 
```bash
nano config.php
```
```bash
change define('SERVER_ROOT', '/group14_v1'); -> define('SERVER_ROOT', '<Your Server Path>/UoJ_IDMS');
```
#### import shema.sql 
```bash
mysql -h <hostname> -u <username> -p <password> < '<PathToProject>/config/shema.sql'
```
##### if you are get any error due to database already existance drop indus_diary and do it again
#### config database setup localhost, username, password, database = indus_diary
```bash
nano <pathToProject>/php/config/db.php

