document.addEventListener('DOMContentLoaded', function(){
    const cartSummary = document.getElementById('cartSummary');
    const payBtn = document.getElementById('payBtn');
    const phoneInput = document.getElementById('phone');
    const status = document.getElementById('status');

    let intasend;
    let currentOrderId = null;

    // Initialize IntaSend
    intasend = new window.IntaSend({
        publicAPIKey: "ISPubKey_test_5814b9ff-2601-473f-b646-8a9d219db264",
        live: false
    })
    .on("COMPLETE", async (results) => {
        console.log("Payment complete", results);
        status.textContent = 'Payment successful! Updating order...';
        // Update order status
        try {
            const res = await fetch('../php/update_order_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({order_id: currentOrderId, status: 'paid', transaction_id: results.id})
            });
            const data = await res.json();
            if(data.status === 'success'){
                status.textContent = 'Order completed successfully!';
                // Redirect or show success
                setTimeout(() => window.location.href = '../html/menu.html', 2000);
            } else {
                status.textContent = 'Order update failed: ' + data.message;
            }
        } catch(err) {
            status.textContent = 'Error updating order: ' + err.message;
        }
    })
    .on("FAILED", (results) => {
        console.log("Payment failed", results);
        status.textContent = 'Payment failed. Please try again.';
        payBtn.disabled = false;
    })
    .on("IN-PROGRESS", (results) => {
        console.log("Payment in progress", results);
        status.textContent = 'Processing payment...';
    });

    async function loadCart(){
        cartSummary.textContent = 'Loading cart...';
        try{
            const res = await fetch('../php/fetch_cart.php');
            const data = await res.json();
            if(!Array.isArray(data) || data.length===0){
                cartSummary.innerHTML = '<em>Your cart is empty.</em>';
                payBtn.disabled = true;
                return;
            }

            let html = '<ul class="cart-list">';
            let total = 0;
            data.forEach(item=>{
                html += `<li>${item.name} × ${item.quantity} — KES ${Number(item.price*item.quantity).toFixed(2)}</li>`;
                total += item.price * item.quantity;
            });
            html += `</ul><div class="cart-total">Total: <strong>KES ${total.toFixed(2)}</strong></div>`;
            cartSummary.innerHTML = html;
            payBtn.disabled = false;
        }catch(err){
            cartSummary.textContent = 'Failed to load cart.';
            payBtn.disabled = true;
        }
    }

    payBtn.addEventListener('click', async ()=>{
        const phone = phoneInput.value.trim();
        if(!/^2547\d{8}$/.test(phone)){
            status.textContent = 'Enter phone in format 2547XXXXXXXX';
            return;
        }

        payBtn.disabled = true;
        status.textContent = 'Creating order...';

        try{
            // 1) Create order on server (reuse place_order.php)
            const orderRes = await fetch('../php/place_order.php', {method:'POST'});
            const orderData = await orderRes.json();
            if(orderData.status !== 'success'){
                status.textContent = 'Failed to create order: ' + (orderData.message||'');
                payBtn.disabled = false;
                return;
            }

            currentOrderId = orderData.order_id;
            status.textContent = 'Order created (ID: '+currentOrderId+'). Initiating payment...';

            // Get total from cart
            const cartRes = await fetch('../php/fetch_cart.php');
            const cartData = await cartRes.json();
            let total = 0;
            cartData.forEach(item => total += item.price * item.quantity);

            // 2) Initiate IntaSend payment
            intasend.run({
                amount: total,
                currency: 'KES',
                phone_number: phone,
                api_ref: 'order_' + currentOrderId // optional reference
            });

        }catch(err){
            status.textContent = 'Error: ' + err.message;
            payBtn.disabled = false;
        }
    });

    loadCart();
});
