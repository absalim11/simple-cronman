# SIMPLE CRON MANAGER

## Workflow

1. **Engine ON**
   - Start by adding executable permissions to the files `unlock.sh` & `lock.sh`
   - `chmod +x unlock.sh lock.sh`
   - Run `./unlock.sh`

2. **Engine OFF**
   - Always execute `./lock.sh` for security if the system is no longer in use.

3. **Disclaimer**
   - Use wisely as it involves sudoers.
   - Default user is `www-data`

## Schedule Settings

Each column in the 'Cron Schedule' accepts numbers or special symbols:

- `**` : Every (for example, `*` in the Minute column means every minute).
- `*`  : List of values (for example, `0,15,30` in Minute means at minutes 0, 15, and 30).
- `-`  : Range of values (for example, `9-17` in Hour means from 9 AM to 5 PM).
- `/`  : Step (for example, `*/5` in Minute means every 5 minutes).

Here are some examples:

| Description                                    | Minute | Hour   | Day of Month | Month | Day of Week  |
|-----------------------------------------------|--------|--------|--------------|-------|--------------|
| Every Minute                                   | `*`    | `*`    | `*`          | `*`   | `*`          |
| Every 5 Minutes                                | `*/5`  | `*`    | `*`          | `*`   | `*`          |
| Every Hour (at minute 0)                        | `0`    | `*`    | `*`          | `*`   | `*`          |
| Every Day at 3 AM                               | `0`    | `3`    | `*`          | `*`   | `*`          |
| Every Day at 3:30 AM                            | `30`   | `3`    | `*`          | `*`   | `*`          |
| Every Monday at 9 AM                            | `0`    | `9`    | `*`          | `*`   | `1`          |
| Every Workday (Monday-Friday) at 5 PM          | `0`    | `17`   | `*`          | `*`   | `1-5`        |
| First Day of Every Month at 00:00              | `0`    | `0`    | `1`          | `*`   | `*`          |
| Every Sunday at 10 AM                          | `0`    | `10`   | `*`          | `*`   | `0` or `7`   |
| Every Hour at minutes 0 and 30                  | `0,30` | `*`    | `*`          | `*`   | `*`          |
| Every Day at 8 AM and 8 PM                      | `0`    | `8,20` | `*`          | `*`   | `*`          |


##
buy me some coffe
**abysalim007@gmail.com**