## SIMPLE CRON MANAJER

## Kebutuhan
- PHP 7+

## Workflow
1. **Engine ON**
	- mulai dengan menambahkan hak akses executable pada file unlock.sh & lock.sh
	- `chmod +x unlock.sh lock.sh`
	- jalankan `./unlock.sh`

2. **Engine OFF**
	- selalu eksekusi `./lock.sh` untuk keamanan jika sistem sudah tidak digunakan.

3. **Disclaimer**
	- gunakan dengan bijak karena melibatkan sudoers. 
	- default use `www-data`

## Pengaturan Schedule

Setiap kolom 'Cron Schedule' menerima angka atau simbol khusus:

- `**` : Setiap (misalnya, `*` di kolom Minute berarti setiap menit).
- `*`  : Daftar nilai (misalnya, `0,15,30` di Minute berarti pada menit ke-0, 15, dan 30).
- `-`  : Rentang nilai (misalnya, `9-17` di Hour berarti dari jam 9 pagi sampai jam 5 sore).
- `/`  : Langkah (misalnya, `*/5` di Minute berarti setiap 5 menit).

Berikut adalah beberapa contoh :

| Deskripsi 							   		| Minute | Hour   | Day of Month | Month | Day of Week  |
|-----------------------------------------------|--------|--------|--------------|-------|--------------|
| Setiap Menit 							   		| `*` 	 | `*`    | `*` 		 | `*` 	 | `*` 		   	|
| Setiap 5 Menit 						   		| `*/5`  | `*`    | `*`  		 | `*`   | `*` 		   	|
| Setiap Jam (pada menit ke-0) 			   		| `0`    | `*`    | `*` 		 | `*` 	 | `*` 		   	|
| Setiap Hari pada Jam 3 Pagi 			   		| `0`    | `3`    | `*` 		 | `*` 	 | `*`   	   	|
| Setiap Hari pada Jam 3:30 Pagi 		   		| `30`   | `3`    | `*` 		 | `*` 	 | `*` 		   	|
| Setiap Hari Senin Jam 9 Pagi 			   		| `0`    | `9`    | `*`  		 | `*` 	 | `1` 		   	|
| Setiap Hari Kerja (Senin-Jumat) Jam 5 Sore 	| `0`    | `17`   | `*` 		 | `*` 	 | `1-5` 	   	|
| Setiap Bulan 1 Setiap Bulan Jam 00:00 		| `0`    | `0`    | `1` 		 | `*` 	 | `*` 		    |
| Setiap Hari Minggu Jam 10 Pagi 				| `0`    | `10`   | `*` 		 | `*` 	 | `0` atau `7` |
| Setiap Jam pada menit ke-0 dan ke-30 			| `0,30` | `*`    | `*` 		 | `*` 	 | `*` 			|
| Setiap Hari pada Jam 8 Pagi dan 8 Malam 		| `0`    | `8,20` | `*`  		 | `*` 	 | `*` 			|


##
ngopi yuk
**abysalim007@gmail.com**