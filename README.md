# WooCommerce Partial Payment Plugin

This WooCommerce plugin adds support for **Partial Payments** on checkout. It allows the admin to define a partial payment percentage and gives customers the option to pay only a portion of the total amount during checkout. The remaining due is displayed and recorded in the order details for both the admin and customer.

## 🧩 Features

- **Custom Setting Field in WooCommerce General Settings**  
  Add a new field in **WooCommerce > Settings > General** to define the **Partial Payment (%)**.
  
- **Partial Payment Option on Checkout Page**  
  Display a **checkbox** at checkout that allows customers to opt-in for partial payment.  
  When selected, the cart total is updated to reflect the partial payment amount.

- **Order Summary on Thank You Page**  
  After payment, on the **Order Received (Thank You) page**, the customer will see:  
  - **Total Price**  
  - **Partial Payment Amount**  
  - **Due Payment Amount**

- **Admin Order Overview**  
  In the **WooCommerce Orders table**, the admin can view:  
  - **Partial Payment Amount**  
  - **Due Payment Amount**

## ⚙️ How It Works

1. Go to **WooCommerce > Settings > General**.
2. Enter a percentage value in the **Partial payment settingst (%)** field and save.
3. On the checkout page, a **checkbox** will appear for customers to opt for partial payment.
4. If selected, the checkout total updates based on the percentage.
5. Once the order is placed, both the customer  will see:
   - Full Order Total
   - Partial Paid Amount
   - Remaining Due Amount

## 📌 Use Case Example

If the partial payment percentage is set to **30%**, and a product costs **$100**:
- Customer can pay **$30** upfront
- Remaining **$70** is marked as due

## 🛠️ Installation

1. Download ZIP file.
2. Upload the plugin folder to `/wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Configure the **Partial Payment** setting in **WooCommerce > Settings > General**.


**Built with ❤️ by Shuvo Gosh**
