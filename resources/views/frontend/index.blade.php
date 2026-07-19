<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>SkyRefund</title>

    <link rel="stylesheet"
        href="{{ asset('css/styles.css') }}">

</head>

<body>

<div class="form-container">

    <!-- ===========================
            PAGE HEADER
    ============================ -->

    <div class="page-header">

        <img src="{{ asset('images/logo.png') }}"
            alt="SkyRefund Logo">

        <p class="eyebrow">
            Sky Refund
        </p>

        <h1>
            Welcome to your refund portal
        </h1>

        <p>
            Start with your airline and contact information,
            then continue to the refund page to submit your
            refund request and supporting documents.
        </p>

    </div>

    <!-- ===========================
            STEP 1
    ============================ -->

    <section
        id="welcomeSection"
        class="form-section">

        <h2>
            Step 1: Passenger Information
        </h2>

        <form id="welcomeForm">

            <!-- Airline -->

            <label for="welcome_airline">
                Airline
            </label>

            <select
    id="welcome_airline"
    name="airline_id"
    required>

    <option value="">
        Loading airlines...
    </option>

</select>

            <!-- First Name -->

            <label for="welcome_first_name">
                First Name
            </label>

            <input
                type="text"
                id="welcome_first_name"
                name="first_name"
                required>

            <!-- Last Name -->

            <label for="welcome_last_name">
                Last Name
            </label>

            <input
                type="text"
                id="welcome_last_name"
                name="last_name"
                required>

            <!-- Email -->

            <label for="welcome_email">
                Email Address
            </label>

            <input
                type="email"
                id="welcome_email"
                name="email"
                required>

            <!-- Phone -->

            <label for="welcome_phone">
                Phone Number
            </label>

            <input
                type="tel"
                id="welcome_phone"
                name="phone"
                required>

            <!-- Address -->

            <label for="welcome_address">
                Residential Address
            </label>

            <input
                type="text"
                id="welcome_address"
                name="address"
                required>

            <div class="button-row">

                <button
                    type="submit">

                    Continue to Refund Form

                </button>

            </div>

        </form>

    </section>

    <!-- ===========================
            STEP 2
    ============================ -->

    <section
        id="refundSection"
        class="form-section hidden">

        <div class="summary-card">

            <p>

                <strong>Airline:</strong>

                <span id="summaryAirline"></span>

            </p>

            <p>

                <strong>Name:</strong>

                <span id="summaryName"></span>

            </p>

            <p>

                <strong>Email:</strong>

                <span id="summaryEmail"></span>

            </p>

            <p>

                <strong>Phone:</strong>

                <span id="summaryPhone"></span>

            </p>

            <p>

                <strong>Address:</strong>

                <span id="summaryAddress"></span>

            </p>

        </div>

        <h2>

            Step 2: Refund Request

        </h2>

        <form
            id="refundForm"
            enctype="multipart/form-data">

            <!-- Hidden Passenger Details -->

            <input
                type="hidden"
                name="airline_id"
                id="hidden_airline_id">

            <input
                type="hidden"
                name="first_name"
                id="hidden_first_name">

            <input
                type="hidden"
                name="last_name"
                id="hidden_last_name">

            <input
                type="hidden"
                name="email"
                id="hidden_email">

            <input
                type="hidden"
                name="phone"
                id="hidden_phone">

            <input
                type="hidden"
                name="address"
                id="hidden_address">
                            <!-- ==========================================
                    TICKET INFORMATION
            =========================================== -->

            <h3>Ticket Information</h3>

            <div id="ticketsContainer">

                <div class="ticket-card">

                    <h4>Ticket 1</h4>

                    <label>Booking Reference (PNR)</label>

                    <input
                        type="text"
                        name="tickets[0][booking_reference]"
                        required>

                    <label>Ticket Number</label>

                    <input
                        type="text"
                        name="tickets[0][ticket_number]"
                        required>

                    <label>Passenger Name</label>

                    <input
                        type="text"
                        name="tickets[0][passenger_name]"
                        required>

                    <label>Flight Number</label>

                    <input
                        type="text"
                        name="tickets[0][flight_number]"
                        required>

                    <label>Airline Code</label>

                    <input
                        type="text"
                        name="tickets[0][airline_code]"
                        required>

                    <input
                        type="hidden"
                        id="ticket_airline_code_0"
                        name="tickets[0][airline_code]">

                    <label>Origin Airport</label>

                    <select
                        name="tickets[0][origin_airport]"
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
                        name="tickets[0][destination_airport]"
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
                        name="tickets[0][departure_datetime]"
                        required>

                    <label>Remarks (Optional)</label>

                    <textarea
                        name="tickets[0][remarks]"
                        rows="3"></textarea>

                </div>

            </div>

            <div class="button-row">

                <button
                    type="button"
                    class="secondary"
                    id="addTicket">

                    Add Another Ticket

                </button>

            </div>

            <!-- ==========================================
                    REFUND DETAILS
            =========================================== -->

            <h3>Refund Information</h3>

            <label for="refund_reason">

                Reason for Refund

            </label>

            <select
                id="refund_reason"
                name="refund_reason"
                required>

                <option value="">-- Select Reason --</option>

                <option value="FLIGHT_CANCELLATION">
                    Cancelled Flight
                </option>

                <option value="FLIGHT_DELAY">
                    Flight Delay
                </option>

                <option value="SCHEDULE_CHANGE">
                    Schedule change
                </option>

                <option value="DUPLICATE_BOOKING">
                    Double Ticketing
                </option>

                <option value="OVERBOOKING">
                    Overbooking 
                </option>

                <option value="PERSONAL">
                    Voluntary Refund
                </option>

                <option value="MEDICAL">
                    Medical Reason
                </option>

                <option value="VISA_DENIAL">
                    Visa Denial
                </option>

                <option value="other">
                    Other
                </option>

            </select>

            <label for="refund_type">

                Refund Type

            </label>

            <select
                id="refund_type"
                name="refund_type"
                required>

                <option value="">
                    -- Select Refund Type --
                </option>

                <option value="INDIVIDUAL">
                    Individual
                </option>

                <option value="THRID_PARTY">
                    Third Party
                </option>

            </select>

            <label for="passenger_explanation">

                Explain Your Refund Request

            </label>

            <textarea
                id="passenger_explanation"
                name="passenger_explanation"
                rows="6"
                required></textarea>
                            <!-- ==========================================
                    BANK DETAILS
            =========================================== -->

            <h3>Bank Details</h3>

            <label for="bank_name">
                Bank Name
            </label>

            <input
                type="text"
                id="bank_name"
                name="bank_name"
                required>

            <label for="account_name">
                Account Name
            </label>

            <input
                type="text"
                id="account_name"
                name="account_name"
                required>

            <label for="account_number">
                Account Number
            </label>

            <input
                type="text"
                id="account_number"
                name="account_number"
                maxlength="10"
                required>

            <label for="account_type">
                Account Type
            </label>

            <select
                id="account_type"
                name="account_type"
                required>

                <option value="">
                    -- Select Account Type --
                </option>

                <option value="SAVINGS">
                    Savings
                </option>

                <option value="CURRENT">
                    Current
                </option>

                <option value="DOMICILIARY">
                    Domiciliary
                </option>

            </select>

            <!-- ==========================================
                    SUPPORTING DOCUMENTS
            =========================================== -->

        
<h3>Supporting Documents</h3>

<label for="signature">
    Signature
</label>

<input
    type="file"
    id="signature"
    accept=".jpg,.jpeg,.png,.pdf">


<label for="passenger_id">
    Passenger ID
</label>

<input
    type="file"
    id="passenger_id"
    accept=".jpg,.jpeg,.png,.pdf">


<label for="account_holder_id">
    Account Holder ID
</label>

<input
    type="file"
    id="account_holder_id"
    accept=".jpg,.jpeg,.png,.pdf">


<label for="additional_documents">
    Additional Documents
</label>

<input
    type="file"
    id="additional_documents"
    accept=".jpg,.jpeg,.png,.pdf">



            <!-- ==========================================
                    CONSENT
            =========================================== -->

            <label class="checkbox-label">

                <input
                    type="checkbox"
                    id="consent"
                    name="consent"
                    value="1"
                    required>

                I certify that all the information provided is accurate and I authorize SkyRefund to process my refund request.

            </label>

            <!-- ==========================================
                    BUTTONS
            =========================================== -->

            <div class="button-row">

                <button
                    type="submit"
                    id="submitBtn">

                    Submit Refund Request

                </button>

                <button
                    type="button"
                    id="backToWelcome"
                    class="secondary">

                    Edit Passenger Details

                </button>

            </div>

        </form>

    </section>

    <div id="statusMessage"></div>

</div>

<script src="{{ asset('js/app.js') }}"></script>

</body>

</html>