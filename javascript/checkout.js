document.addEventListener('DOMContentLoaded', function(){
    const cartSummary = document.getElementById('cartSummary');
    const payBtn = document.getElementById('payBtn');
    const phoneInput = document.getElementById('phone');
    const status = document.getElementById('status');

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

            const orderId = orderData.order_id;
            status.textContent = 'Order created (ID: '+orderId+'). Initiating M-Pesa...';

            // 2) Initiate M-Pesa STK Push
            const mpesaRes = await fetch('../php/initiate_payment.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({order_id: orderId, phone})
            });

            const mpesaData = await mpesaRes.json();
            if(mpesaData.status === 'success'){
                status.textContent = 'STK Push sent. Check your phone to complete payment.';
            } else {
                status.textContent = 'Payment initiation failed: ' + (mpesaData.message||mpesaData.error||'');
                payBtn.disabled = false;
            }

        }catch(err){
            status.textContent = 'Error: ' + err.message;
            payBtn.disabled = false;
        }
    });

    loadCart();
});
