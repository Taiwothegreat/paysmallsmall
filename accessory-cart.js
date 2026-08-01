// ==========================================
// ACCESSORY CART
// ==========================================

let accessoryCart = JSON.parse(
    localStorage.getItem("accessoryCart") || "[]"
);

// ==========================================
// ADD TO CART
// ==========================================

function addAccessoryToCart(product) {
        console.log("Adding:", product);

    console.log("Cart before:", accessoryCart);
    const existing = accessoryCart.find(
        item => item.name === product.name
    );

    if (existing) {

        existing.qty++;

    } else {

        accessoryCart.push({

            ...product,
            qty: 1

        });

    }
console.log("Cart after:", accessoryCart);
    saveAccessoryCart();

    renderAccessoryCart();

}

// ==========================================
// SAVE
// ==========================================

function saveAccessoryCart() {

    localStorage.setItem(
        "accessoryCart",
        JSON.stringify(accessoryCart)
    );

}

// ==========================================
// REMOVE
// ==========================================

function removeAccessory(index) {

    accessoryCart.splice(index,1);

    saveAccessoryCart();

    renderAccessoryCart();

}

// ==========================================
// INCREASE
// ==========================================

function increaseQty(index){

    accessoryCart[index].qty++;

    saveAccessoryCart();

    renderAccessoryCart();

}

// ==========================================
// DECREASE
// ==========================================

function decreaseQty(index){

    accessoryCart[index].qty--;

    if(accessoryCart[index].qty<=0){

        accessoryCart.splice(index,1);

    }

    saveAccessoryCart();

    renderAccessoryCart();

}

// ==========================================
// CHECKOUT
// ==========================================

function checkoutAccessories(){

    // Check if cart is empty
    if (accessoryCart.length === 0) {

        alert("Your cart is empty.");

        return;

    }

    // Check if LGA is selected
    const lgaSelect =
    document.getElementById("accessoryLGASelect");

    if (!lgaSelect || !lgaSelect.value) {

        alert("Please select your Delivery LGA/LCDA.");

        return;

    }

    // Save selected LGA
    localStorage.setItem(
        "customerLGA",
        lgaSelect.value
    );
const customerAddress = document
    .getElementById("accessoryCustomerAddress")
    .value
    .trim();

if (!customerAddress) {
    alert("Please enter your Delivery Address.");
    return;
}
console.log("Address entered:", customerAddress);
localStorage.setItem("customerAddress", customerAddress);
    // Save accessory cart
    localStorage.setItem(
        "accessoryCheckout",
        JSON.stringify(accessoryCart)
    );

    // Go to payment page
    window.location.href = "accessory-payment.html";

}
// ==========================================
// RENDER CART
// ==========================================

function renderAccessoryCart(){

    const cart = document.getElementById("accessoryCart");
    const items = document.getElementById("cartItems");
    const subtotal = document.getElementById("cartSubtotal");
    const count = document.getElementById("cartCount");

    if(!cart) return;

    if(accessoryCart.length === 0){
        cart.style.display = "none";
        return;
    }

    cart.style.display = "block";

    items.innerHTML = "";

    let total = 0;

    accessoryCart.forEach((item,index)=>{

        total += item.price * item.qty;

        items.innerHTML += `
        <div class="media" style="margin-bottom:15px;">

            <div class="media-left">
                <img src="${item.image}"
                style="width:60px;height:60px;object-fit:cover;">
            </div>

            <div class="media-body">

                <strong>${item.name}</strong>

                <br>

                ₦${item.price.toLocaleString()}

                <br><br>

                <button class="btn btn-default btn-xs"
                onclick="decreaseQty(${index})">−</button>

                <strong style="padding:0 10px;">
                ${item.qty}
                </strong>

                <button class="btn btn-default btn-xs"
                onclick="increaseQty(${index})">+</button>

                <button
                class="btn btn-danger btn-xs pull-right"
                onclick="removeAccessory(${index})">

                Remove

                </button>

            </div>

        </div>
        `;

    });

    subtotal.innerHTML = total.toLocaleString();

    count.innerHTML = accessoryCart.length;
}
function loadAccessoryLGAs() {

    const select =
        document.getElementById("accessoryLGASelect");

    if (!select) return;

    // Prevent duplicates if called more than once
    if (select.options.length > 1) return;

    Object.keys(accessoryLGACharges).forEach(function(lga) {

        const option =
            document.createElement("option");

        option.value = lga;
        option.textContent = lga;

        select.appendChild(option);

    });

}

document.addEventListener("DOMContentLoaded", function () {

    renderAccessoryCart();

    loadAccessoryLGAs();

});