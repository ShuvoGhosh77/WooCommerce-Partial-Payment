jQuery(document).ready(function($) {
  
    let partialPaymentPercentage = partialPaymentData.percentage / 100; 

    // Check if the Partial Payment checkbox is checked
    const partialPaymentCheckbox = $('#partial_payment'); 
    const originalTotalText = parseFloat($('.order-total .amount').text().replace('€', '').replace(',', '.')); // Get the original total
    let originalTotal = parseFloat(originalTotalText);
    // Set the partial payment amount
    let partialAmount = originalTotal * partialPaymentPercentage; 


    // Update the total when the checkbox is clicked
    partialPaymentCheckbox.change(function () {
        if ($(this).is(':checked')) {
            // Update the total to the partial amount
            updateOrderTotal(partialAmount);
           
        } else {
            // Revert to the original total
            updateOrderTotal(originalTotal);
        }
    });

    // Function to update the total displayed on the checkout page
    function updateOrderTotal(newTotal) {
        $('.order-total .amount').text('€' + newTotal);
        // Trigger WooCommerce update to recalculate order review
        $('body').trigger('update_checkout');
    }

    
});
