// ======================================================
// ELEMENTS
// ======================================================
console.log("APP.JS LOADED");
const welcomeForm = document.getElementById("welcomeForm");
const refundForm = document.getElementById("refundForm");

const welcomeSection = document.getElementById("welcomeSection");
const refundSection = document.getElementById("refundSection");

const statusMessage = document.getElementById("statusMessage");

const submitBtn = document.getElementById("submitBtn");
const backToWelcome = document.getElementById("backToWelcome");

// Summary

const summaryAirline = document.getElementById("summaryAirline");
const summaryName = document.getElementById("summaryName");
const summaryEmail = document.getElementById("summaryEmail");
const summaryPhone = document.getElementById("summaryPhone");
const summaryAddress = document.getElementById("summaryAddress");

// Hidden Inputs

const hiddenAirline = document.getElementById("hidden_airline_id");
const hiddenFirstName = document.getElementById("hidden_first_name");
const hiddenLastName = document.getElementById("hidden_last_name");
const hiddenEmail = document.getElementById("hidden_email");
const hiddenPhone = document.getElementById("hidden_phone");
const hiddenAddress = document.getElementById("hidden_address");

    // ======================================================
// LOAD AIRLINES
// ======================================================

async function loadAirlines() {

    console.log("loadAirlines started");

    const select = document.getElementById("welcome_airline");

    try {

        const response = await fetch("http://127.0.0.1:8000/api/v1/airlines");

        console.log("Status:", response.status);

        const result = await response.json();

        console.log("Response:", result);

        select.innerHTML = '<option value="">-- Select Airline --</option>';

        result.data.forEach(airline => {

            console.log("Airline:", airline);

            const option = document.createElement("option");

            option.value = airline.id;
            option.textContent = airline.name;
            option.dataset.code = airline.code;

            select.appendChild(option);
        });

    } catch (error) {

        console.error("Fetch error:", error);

        select.innerHTML =
            '<option value="">Unable to load airlines</option>';

    }

}

loadAirlines();
// ======================================================
// STEP ONE
// ======================================================

welcomeForm.addEventListener("submit", function (e) {

    e.preventDefault();

    const airlineSelect = document.getElementById("welcome_airline");

    const airlineId = airlineSelect.value;

    const airlineCode =
    airlineSelect.options[airlineSelect.selectedIndex].dataset.code;

    document.getElementById("ticket_airline_code_0").value = airlineCode;

    const airlineName =
        airlineSelect.options[airlineSelect.selectedIndex].text;

    const firstName =
        document.getElementById("welcome_first_name").value;

    const lastName =
        document.getElementById("welcome_last_name").value;

    const email =
        document.getElementById("welcome_email").value;

    const phone =
        document.getElementById("welcome_phone").value;

    const address =
        document.getElementById("welcome_address").value;

    // ===========================
    // Summary Card
    // ===========================

    summaryAirline.textContent = airlineName;

    summaryName.textContent =
        `${firstName} ${lastName}`;

    summaryEmail.textContent = email;

    summaryPhone.textContent = phone;

    summaryAddress.textContent = address;



    // ===========================
    // Hidden Fields
    // ===========================

    hiddenAirline.value = airlineId;

    hiddenFirstName.value = firstName;

    hiddenLastName.value = lastName;

    hiddenEmail.value = email;

    hiddenPhone.value = phone;

    hiddenAddress.value = address;

    // ===========================
    // Show Refund Form
    // ===========================

    welcomeSection.classList.add("hidden");

    refundSection.classList.remove("hidden");

});


// ======================================================
// BACK BUTTON
// ======================================================

backToWelcome.addEventListener("click", function () {

    refundSection.classList.add("hidden");

    welcomeSection.classList.remove("hidden");

});


// ======================================================
// STATUS MESSAGE
// ======================================================

function showMessage(message, success = true) {

    statusMessage.textContent = message;

    statusMessage.className = success
        ? "success"
        : "error";

}
// ======================================================
// DYNAMIC TICKETS
// ======================================================

const ticketsContainer = document.getElementById("ticketsContainer");
const addTicketBtn = document.getElementById("addTicket");

let ticketIndex = 1;

addTicketBtn.addEventListener("click", function () {

    const ticket = document.createElement("div");

    ticket.className = "ticket-card";

    ticket.innerHTML = `

        <hr>

        <h4>Ticket ${ticketIndex + 1}</h4>

        <label>Booking Reference (PNR)</label>

        <input
            type="text"
            name="tickets[${ticketIndex}][booking_reference]"
            required>

        <label>Ticket Number</label>

        <input
            type="text"
            name="tickets[${ticketIndex}][ticket_number]"
            required>

        <label>Passenger Name</label>

        <input
            type="text"
            name="tickets[${ticketIndex}][passenger_name]"
            required>

        <label>Flight Number</label>

        <input
            type="text"
            name="tickets[${ticketIndex}][flight_number]"
            required>

        <label>Airline Code</label>

        <input
            type="text"
            name="tickets[${ticketIndex}][airline_code]"
            required>

        <label>Origin Airport</label>

        <select
            name="tickets[${ticketIndex}][origin_airport]"
            required>

            <option value="">-- Select Origin --</option>

            <option value="ABV">Abuja (ABV)</option>
            <option value="ABB">Asaba (ABB)</option>
            <option value="BNI">Benin (BNI)</option>
            <option value="CBQ">Calabar (CBQ)</option>
            <option value="KAN">Kano (KAN)</option>
            <option value="LOS">Lagos (LOS)</option>
            <option value="PHC">Port Harcourt (PHC)</option>
            <option value="SKO">Sokoto (SKO)</option>
            <option value="QRW">Warri (QRW)</option>
            <option value="YOL">Yola (YOL)</option>

        </select>

        <label>Destination Airport</label>

        <select
            name="tickets[${ticketIndex}][destination_airport]"
            required>

            <option value="">-- Select Destination --</option>

            <option value="ABV">Abuja (ABV)</option>
            <option value="ABB">Asaba (ABB)</option>
            <option value="BNI">Benin (BNI)</option>
            <option value="CBQ">Calabar (CBQ)</option>
            <option value="KAN">Kano (KAN)</option>
            <option value="LOS">Lagos (LOS)</option>
            <option value="PHC">Port Harcourt (PHC)</option>
            <option value="SKO">Sokoto (SKO)</option>
            <option value="QRW">Warri (QRW)</option>
            <option value="YOL">Yola (YOL)</option>

        </select>

        <label>Departure Date & Time</label>

        <input
            type="datetime-local"
            name="tickets[${ticketIndex}][departure_datetime]"
            required>

        <label>Remarks</label>

        <textarea
            name="tickets[${ticketIndex}][remarks]"
            rows="3"></textarea>

        <div class="button-row">

            <button
                type="button"
                class="secondary remove-ticket">

                Remove Ticket

            </button>

        </div>

    `;

    ticketsContainer.appendChild(ticket);

    ticketIndex++;

});


// ======================================================
// REMOVE TICKET
// ======================================================

ticketsContainer.addEventListener("click", function (e) {

    if (!e.target.classList.contains("remove-ticket")) {
        return;
    }

    const cards = ticketsContainer.querySelectorAll(".ticket-card");

    if (cards.length === 1) {

        alert("At least one ticket is required.");

        return;

    }

    e.target.closest(".ticket-card").remove();

});
// ======================================================
// SUBMIT REFUND FORM
// ======================================================

refundForm.addEventListener("submit", async function (e) {

    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.textContent = "Submitting...";

    statusMessage.innerHTML = "";

    try {

        const formData = new FormData();
        // copy all normal form fields except file inputs

        const fields= refundForm.querySelectorAll(
            "input:not([type='file']), select, textarea"
        );

        fields.forEach(field => {
            if(!field.name) return;

            if (field.type === "checkbox") {
                if (field.checked){
                    formData.append(field.name, "1");
                }
                return;
            }
            formData.append(field.name, field.value);
        });

        // =========================================
// Attachments
// =========================================

const attachmentInputs = [

    {
        id: "signature",
        type: "signature"
    },

    {
        id: "passenger_id",
        type: "passenger_id"
    },

    {
        id: "account_holder_id",
        type: "account_holder_id"
    },

    {
        id: "authorization_letter",
        type: "authorization_letter"
    },

    {
        id: "other_document",
        type: "other"
    }

];

attachmentInputs.forEach(item => {

    const input = document.getElementById(item.id);

    if (!input) return;

    if (input.files.length === 0) return;

    formData.append(
        "attachments[]",
        input.files[0]
    );

    formData.append(
        "attachment_types[]",
        item.type
    );

});

const response = await fetch(
    "http://127.0.0.1:8000/api/v1/refunds",
    {
        method: "POST",
        headers: {
            Accept: "application/json"
        },
        body: formData
    }
);

const result = await response.json();

console.log("Status:", response.status);
console.log("Result:", result);

if (!response.ok) {
    alert(result.error || result.message);
}
        // ==========================
        // SUCCESS
        // ==========================

        if (response.ok) {

            showMessage(
                `Refund submitted successfully! Reference: ${result.reference}`,
                true
            );

            refundForm.reset();

            welcomeForm.reset();

            refundSection.classList.add("hidden");

            welcomeSection.classList.remove("hidden");

            ticketIndex = 1;

            // Remove dynamically-added tickets
            document
                .querySelectorAll("#ticketsContainer .ticket-card")
                .forEach((card, index) => {

                    if (index > 0) {
                        card.remove();
                    }

                });

            return;
        }

        // ==========================
        // VALIDATION ERRORS (422)
        // ==========================

        if (response.status === 422) {

            let html = "<ul>";

            Object.values(result.errors).forEach(messages => {

                messages.forEach(message => {

                    html += `<li>${message}</li>`;

                });

            });

            html += "</ul>";

            statusMessage.innerHTML = html;

            statusMessage.className = "error";

            return;
        }

        // ==========================
        // OTHER ERRORS
        // ==========================

        showMessage(

            result.message || "Unable to submit refund request.",

            false

        );

    } catch (error) {

        console.error(error);

        showMessage(

            "Unable to connect to the server.",

            false

        );

    } finally {

        submitBtn.disabled = false;

        submitBtn.textContent = "Submit Refund Request";

    }

});