<?php
/*
Plugin Name: Import kuponów
Description: Import kuponów rabatowych z pliku .csv do WooCommerce
Version: 1.0
Author: Marcin Trzciński
*/

// Dodaj menu do panelu administracyjnego WordPress
add_action('admin_menu', 'import_kuponow_menu');

function import_kuponow_menu() {
    add_submenu_page(
        'woocommerce-marketing',
        'Import kuponów',
        'Import kuponów',
        'manage_woocommerce',
        'import-kuponow',
        'podstrona_import_kuponow'
    );
}

// Strona importera kuponów
function podstrona_import_kuponow() {
    if (isset($_POST['import_coupons'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
            $csvFilePath = $_FILES['csv_file']['tmp_name'];
            importujKupony($csvFilePath);
        } else {
            echo '<div id="message" class="notice notice-error is-dismissible">
                        <p>Wystąpił błąd importu plików. Sprwadź przy plik jest poprawny.</p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Ukryj komunikat.</span></button>
                    </div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>Import kuponów rabatowych</h1>
        <p>Funkcja umożliwia import kuponów rabatowych z pliku csv. Listę kuponów w Excel należy zapisać jako CSV UTF-8 (rozdzielony przecinkami), a struktura kolumn powinna być następująca:</p>
        <div class="update-nag notice inline">
                <table class="wp-list-table widefat striped table-view-list">
                    <tr><th>coupon_code</th><th>discount_type</th><th>coupon_amount</th><th>expiry_date</th></tr>
                    <tr><td>kod1</td><td>percent</td><td>10</td><td>31.12.2023</td></tr>
                    <tr><td>kod2</td><td>fixed_cart</td><td>5</td><td>05.01.2024</td></tr>
                    <tr><td>mArCin</td><td>percent</td><td>15</td><td>09.01.2024</td></tr>
                    <tr><td>kupon2023</td><td>fixed_cart</td><td>20</td><td>30.01.2024</td></tr>
                </table>
        </div>
        <h1>Prześlij plik</h1>
        <p>Wybierz plik w formacie CSV:</p>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="csv_file">Plik CSV:</label>
            <input type="file" name="csv_file" id="csv_file" />
            <input type="submit" name="import_coupons" value="Importuj kupony" class="button-primary" />
        </form>
    </div>
    <?php
}

// Funkcja importująca kupony z pliku CSV
function importujKupony($csvFilePath) {

    global $wpdb;

    // Ścieżka do pliku CSV
    $file = fopen($csvFilePath, 'r');

    // Odczytaj dane z pliku CSV
    if (($handle = fopen($csvFilePath, "r")) !== false) {
        
        // Pomijamy nagłówek pliku CSV
        fgetcsv($handle, 0, ';');

        while (($line = fgetcsv($handle, 0, ';')) !== false) {
                $coupon_code = isset($line[0]) ? $line[0] : '';
                $discount_type = isset($line[1]) ? $line[1] : '';
                $coupon_amount = isset($line[2]) ? $line[2] : '';
    
                // Konwertuj format daty na 'YYYY-MM-DD'
                $expiry_date = isset($line[3]) ? date('Y-m-d', strtotime($line[3])) : '';
        
        // Sprawdź, czy kupon o danym kodzie już istnieje
        $existing_coupon_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'shop_coupon' AND post_title = %s",
                $coupon_code
            )
        );

        if (!$existing_coupon_id) {
            // Kupon nie istnieje, dodaj nowy kupon
            $coupon_data = array(
                'post_title' => $coupon_code,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon',
            );

            $coupon_id = wp_insert_post($coupon_data);

            // Ustaw właściwości kuponu
            update_post_meta($coupon_id, 'discount_type', $discount_type);
            update_post_meta($coupon_id, 'coupon_amount', $coupon_amount);
            update_post_meta($coupon_id, 'individual_use', 'yes');
            update_post_meta($coupon_id, 'product_ids', '');
            update_post_meta($coupon_id, 'exclude_product_ids', '');
            update_post_meta($coupon_id, 'usage_limit', '');
            update_post_meta($coupon_id, 'expiry_date', $expiry_date);

            echo '<div id="message" class="updated notice notice-success is-dismissible">
                        <p>Kupon o kodzie '.$coupon_code.' został zaimportowany.</p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Ukryj komunikat.</span></button>
                    </div>';

        } else {
            // Kupon już istnieje, pomijamy
            echo '<div id="message" class="notice notice-error is-dismissible">
                        <p>Kupon o kodzie '.$coupon_code.' już istnieje i został pominięty.</p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Ukryj komunikat.</span></button>
                    </div>';

        }
    }

    fclose($file);
    }
}


?>
