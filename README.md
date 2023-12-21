# import-kuponow-WooCommerce

Wtyczka dodaje funkcję importu kuponów rabatowych z pliku csv. 
Listę kuponów w Excel należy zapisać jako CSV UTF-8 (rozdzielony przecinkami), a struktura kolumn powinna być następująca:
-------------------------------------------------------------
| coupon_code	| discount_type	| coupon_amount	| expiry_date |
-------------------------------------------------------------
| kod1	      | percent	      | 10	          | 31.12.2023  |
-------------------------------------------------------------
| kod2	      | fixed_cart	  | 5	            | 05.01.2024  |
-------------------------------------------------------------
| kupon2023	  | fixed_cart	  | 20	          | 30.01.2024  |
-------------------------------------------------------------
