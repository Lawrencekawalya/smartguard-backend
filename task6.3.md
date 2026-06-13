# Phase 6.3 Revision

# Energy Analytics & Consumption Reporting

Read:

* agent-rules.md
* task.md
* existing Phase 6.3 implementation

---

## IMPORTANT

This is NOT a new module.

Phase 6.3 has already been partially implemented.

The current implementation provides:

* Sidebar navigation entry
* Energy Analytics page
* Summary cards
* Date range filter
* Initial API structure

These foundations should be preserved.

The goal of this task is to transform the current implementation into the originally intended Energy Analytics and Reporting platform.

---

# CURRENT PROBLEM

The current page behaves more like another telemetry screen.

Energy Analytics should instead focus on:

* Consumption reporting
* Cost estimation
* Historical analysis
* Management insights
* Energy budgeting

It should complement the SmartGuard Dashboard rather than duplicate it.

---

# ISSUE 1: FIX BROKEN ANALYTICS

The page currently displays:

Failed to load data from:

/api/v1/energy/summary

Investigate and resolve the root cause.

Verify:

* Route registration
* Controller responses
* Service layer
* Authentication requirements
* API Resources
* Data availability
* Exception handling

The page must load successfully.

No red error panels should appear when the system is healthy.

---

# ISSUE 2: DAILY ENERGY CONSUMPTION CHART

Create:

resources/js/components/SmartGuard/DailyEnergyChart.vue

Display:

Daily Consumption (Last 30 Days)

Example:

15 Jun -> 12.4 kWh
16 Jun -> 15.8 kWh
17 Jun -> 9.2 kWh

Requirements:

* ApexCharts
* Responsive
* Dark theme compatible
* Tooltips
* Date labels

This should become the primary chart on the page.

---

# ISSUE 3: WEEKLY ENERGY CONSUMPTION CHART

Create:

resources/js/components/SmartGuard/WeeklyEnergyChart.vue

Display:

Last 12 Weeks

Example:

Week 1 -> 120 kWh
Week 2 -> 135 kWh
Week 3 -> 108 kWh
Week 4 -> 141 kWh

Purpose:

Allow managers to identify consumption trends.

---

# ISSUE 4: MONTHLY ENERGY CONSUMPTION CHART

Create:

resources/js/components/SmartGuard/MonthlyEnergyChart.vue

Display:

Last 12 Months

Example:

Jan -> 320 kWh
Feb -> 350 kWh
Mar -> 410 kWh

Purpose:

Long-term planning and budgeting.

---

# ISSUE 5: ENERGY COST ANALYSIS

Current implementation only shows:

Estimated Cost

This is insufficient.

Create a dedicated cost analysis section.

Display:

| Period | Energy (kWh) | Tariff | Cost    |
| ------ | ------------ | ------ | ------- |
| Today  | 12.4         | 805    | 9,982   |
| Week   | 86.2         | 805    | 69,391  |
| Month  | 342.8        | 805    | 275,954 |

Requirements:

* Dynamic calculations
* Database-driven tariff
* Currency support
* Proper formatting

---

# ISSUE 6: ENERGY REPORT TABLE

Create a reporting table.

Columns:

* Date
* Energy Used (kWh)
* Estimated Cost
* Peak Power (W)
* Fault Count

Example:

| Date       | Energy | Cost   | Peak Power | Faults |
| ---------- | ------ | ------ | ---------- | ------ |
| 2026-06-10 | 15.4   | 12,397 | 1200       | 0      |
| 2026-06-11 | 18.2   | 14,651 | 1500       | 1      |

Purpose:

This becomes the management report used by:

* SACCOs
* Schools
* Hospitals
* Offices
* Factories

---

# ISSUE 7: EXPORT FUNCTIONALITY

Add:

Export CSV

Add:

Export PDF

Exports must respect:

* Selected date range
* Current filters

Generated reports should include:

* Energy consumption
* Cost
* Peak demand
* Fault count

---

# ISSUE 8: ENERGY SETTINGS

Create:

Settings
└── Energy Settings

Separate from:

Settings
└── Fault Thresholds

---

Create:

EnergySettings page

Display:

Tariff Rate
Currency
Description

Default:

Tariff Rate = 805
Currency = UGX
Description = UMEME Residential Tariff

---

Users should be able to:

* View tariff
* Edit tariff
* Save tariff

---

# ISSUE 9: COST CALCULATION ENGINE

Verify:

Cost = Energy(kWh) × Tariff Rate

Example:

125.4 × 805

= 100,947 UGX

All displayed costs must use this calculation.

No hardcoded values.

---

# ISSUE 10: PAGE LAYOUT

Expected final layout:

Energy Analytics

├── Date Range Filter
│
├── Summary Cards
│   ├── Today
│   ├── Week
│   ├── Month
│   └── Estimated Cost
│
├── Daily Consumption Chart
│
├── Weekly Consumption Chart
│
├── Monthly Consumption Chart
│
├── Cost Analysis Section
│
├── Energy Report Table
│
└── Export CSV / PDF

The page must remain responsive.

The page must use the existing theme.

Do not redesign the application.

---

# TESTING

Run:

php artisan test

npm run build

Verify:

* Energy summary loads
* Charts load
* Cost calculations are correct
* Report table displays data
* Exports work
* Energy settings save correctly

Fix all failures.

---

# DOCUMENTATION

Update:

* task.md
* README.md

Document:

* Energy Analytics
* Cost Analysis
* Reporting
* Exporting
* Energy Settings

---

# DELIVERABLES

When complete:

1. Show root cause of analytics loading failure.
2. Show files created.
3. Show files modified.
4. Show Energy Settings implementation.
5. Show chart components created.
6. Show report table implementation.
7. Show export functionality.
8. Show screenshots.
9. Show test results.
10. Show build results.
11. Suggest commit message.
12. Stop and wait for approval.