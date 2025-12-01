# 🍔 Self-Service Fast Food Ordering System  
*PHP + MySQL + WebSockets*

This repository contains a working demonstration of a **real-time ordering workflow system** for a fast-food restaurant, intended for **Web Programming** students as a **capstone group exercise**. It also serves as an introductory bridge to **System Analysis and Design (SAD)**.

---

## 🎯 System Overview

The system simulates multiple terminals working together:

| Terminal | Role | Description |
|---------|------|-------------|
| **Customer Kiosk** | Customer UI | Browse menu, build cart, submit order (cash only) |
| **Teller Dashboard** | Cashier | Confirm order, edit items, accept payment |
| **Kitchen Display** | Kitchen staff | View queue, mark food as ready |
| **Claim Display** | Pickup counter | Displays order status: In-Process / Claim Now |
| **Admin Panel** | Super Admin | Manage terminals, products, categories, bundles |

Communication uses **WebSockets** for live updates between terminals.

---

## 🧩 Technology Stack

| Layer | Technology |
|------|------------|
| Frontend | HTML5, CSS3, Bootstrap, JavaScript |
| Backend | PHP (Procedural) |
| Database | MySQL |
| Real-Time Updates | Ratchet WebSocket Server |
| Version Control | GitHub (Fork and contribute!) |

---

## 📌 Setup Guide (Local Installation)

1️⃣ Clone/Fork this repository  
```sh
git clone <your-fork-link>
