# Self-Service Fast Food Ordering System

This repository contains a working demonstration of a real-time ordering workflow system for a fast-food restaurant, intended for Web Programming students as a capstone group exercise.

## System Overview
The system simulates multiple terminals:
- Customer Kiosk: Browse menu, build cart, submit order (cash only)
- Teller Dashboard: Confirm order, edit items, accept payment
- Kitchen Display: View queue, mark food as ready
- Claim Display: Pickup counter status: In-Process / Claim Now
- Admin Panel: Manage terminals, products, categories, bundles

## Technology Stack
- Front-End: HTML5, CSS3, Bootstrap, JavaScript
- Back-End: PHP
- Database: MySQL
- Real-Time Updates: Ratchet WebSocket Server
- Version Control: GitHub

## Setup Instructions
1. Fork or clone this repository
2. Import the SQL file (ffos.sql) to MySQL database
3. Set database credentials in config.php
4. Run WebSocket server: `php ws-server.php`
5. Access system via browser using `http://localhost/ffos/`

## Terminal Access
Each terminal page requires a valid 6-digit PIN code generated in the Admin Panel. One Super Admin exists in the database seed.

## Group Tasks and Requirements

### 1. System Evaluation
Identify functional defects, UI issues, performance concerns, missing validations. Provide screenshots and severity level.

### 2. Enhancement Proposal
Minimum of 5 improvements, minimum of 3 must be implemented. Must not remove core workflow features. Must have justification based on user need or system weakness.

### 3. Implementation
Apply enhancements through functional code modifications. UI or feature improvements should remain compatible with real-time multi-terminal workflow.

### 4. Documentation
- Evaluation findings
- Proposed enhancements
- Before and after screenshots
- Team member contributions and logs

## Project Constraints
- Order-to-Claim workflow must remain operational
- WebSocket synchronization should not break
- System must continue supporting multiple terminals

## Suggested Enhancements
- Inventory and stock management
- Sales reporting per day
- Sound or animation alert on Claim Display
- Improved kiosk touch UI
- Receipt printing
- Discount or promo management
- Enhanced UI themes
- Employee login roles

## Team Work Requirements
- Each member must commit changes visibly to GitHub
- Work should be distributed and traceable
- Pull requests encouraged

## Instructor Notes
The system is intentionally imperfect for students to evaluate.
Students must apply SAD thinking: Analyze then Improve.

## License
Educational use only. Not for commercial deployment.

## Contribution
Bug fixes and improvements are welcome for educational enhancement.
