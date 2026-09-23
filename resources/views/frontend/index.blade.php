<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>SkyRefund</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="{{ asset('css/styles.css') }}">

</head>

<body>

<div class="form-container">

    <!-- ===========================
            PAGE HEADER
    ============================ -->

    <div class="page-header">

        <img src="{{ asset('image/Refunlogo.jpg') }}"
            alt="SkyRefund Logo"
            class="logo brand-mark">

        <p class="eyebrow">
            Sky Refund
        </p>

        <h1>
            Welcome to your refund portal
        </h1>

        <p>
            Submit a new refund request, or track one you've already
            sent in using your reference number and email.
        </p>

    </div>

    <!-- ===========================
            MODE TOGGLE (new — purely
            navigational, doesn't touch
            any existing form logic)
    ============================ -->

    <div class="mode-toggle">

        <button type="button" id="modeSubmitBtn" class="active">
            Submit a Request
        </button>

        <button type="button" id="modeTrackBtn">
            Track Your Refund
        </button>

    </div>

    <!-- ===========================
            SUBMIT FLOW WRAPPER
    ============================ -->

    <div id="submitFlow">

    <div class="step-indicator" id="stepIndicator">
        <span class="dot active" data-step="1"></span>
        <span class="line"></span>
        <span class="dot" data-step="2"></span>
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

                <strong>Airline</strong>

                <span id="summaryAirline"></span>

            </p>

            <p>

                <strong>Name</strong>

                <span id="summaryName"></span>

            </p>

            <p>

                <strong>Email</strong>

                <span id="summaryEmail"></span>

            </p>

            <p>

                <strong>Phone</strong>

                <span id="summaryPhone"></span>

            </p>

            <p>

                <strong>Address</strong>

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

    </div><!-- /#submitFlow -->
    
    <section id="successSection" class="hidden">

    <div class="success-icon">✓</div>

    <h2>Refund Request Submitted</h2>

    <p class="success-subtitle">
        Thank you, we've received your request and it's now being reviewed.
    </p>

    <div class="reference-box">
        <span>Your Reference Number</span>
        <strong id="successReference">—</strong>
    </div>

    <div class="next-steps">
        <h3>What happens next?</h3>
        <ol>
            <li>Our team will review your submitted documents within <strong>2–3 business days</strong>.</li>
            <li>You'll receive an email at the address you provided with updates on your refund status.</li>
            <li>If anything is missing or unclear, we'll reach out using the contact details you supplied.</li>
            <li>Once approved, refunds are typically processed within <strong>7–14 business days</strong>.</li>
        </ol>
    </div>

    <div class="contact-info">
        <h3>Need help?</h3>
        <p>Email us at <a href="mailto:support@skyrefund.com">support@skyrefund.com</a></p>
        <p>Or call <a href="tel:+2340000000000">+234 000 000 0000</a></p>
    </div>

    <button id="backToHomeBtn" class="primary">Back to Home</button>

</section>
    <!-- ===========================
            TRACK YOUR REFUND (new)
    ============================ -->

    <div id="trackFlow" class="form-section hidden">

        <h2>Track Your Refund</h2>

        <form id="trackForm">

            <label for="track_reference">Reference Number</label>

            <input
                type="text"
                id="track_reference"
                name="reference"
                placeholder="SR-20260101-000123"
                required>

            <label for="track_email">Email Address</label>

            <input
                type="email"
                id="track_email"
                name="email"
                placeholder="The email you submitted your request with"
                required>

            <div class="button-row">

                <button type="submit" id="trackSubmitBtn">
                    Check Status
                </button>

            </div>

        </form>

        <div id="trackResult" class="track-result hidden">

            <div class="track-status-banner">
                <div>
                    <div class="ref" id="trackRefLabel"></div>
                </div>
                <span class="track-status-pill" id="trackStatusPill"></span>
            </div>

            <h3 style="margin-top: 0;">Progress</h3>
            <ul class="track-timeline" id="trackTimeline"></ul>

            <h3>Tickets</h3>
            <div class="track-ticket-list" id="trackTickets"></div>

            <h3>Attachments</h3>
            <div class="track-attachments" id="trackAttachments"></div>

            <h3>Add More Documents</h3>

            <form id="trackUploadForm" enctype="multipart/form-data">

                <label for="track_upload_files">Choose file(s)</label>
                <input type="file" id="track_upload_files" multiple accept=".jpg,.jpeg,.png,.pdf">

                <label for="track_upload_type">Document type</label>
                <select id="track_upload_type">
                    <option value="other">Other</option>
                    <option value="signature">Signature</option>
                    <option value="passenger_id">Passenger ID</option>
                    <option value="account_holder_id">Account Holder ID</option>
                    <option value="authorization_letter">Authorization Letter</option>
                </select>

                <div class="button-row">
                    <button type="submit" id="trackUploadBtn">Upload Document(s)</button>
                </div>

            </form>

            <div id="trackUploadMessage"></div>

        </div>

        <div id="trackMessage"></div>

    </div><!-- /#trackFlow -->

</div>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/track.js') }}"></script>

</body>

</html>