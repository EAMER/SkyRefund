// ======================================================
// MODE TOGGLE — Submit vs Track
// ======================================================

const modeSubmitBtn = document.getElementById("modeSubmitBtn");
const modeTrackBtn = document.getElementById("modeTrackBtn");
const submitFlow = document.getElementById("submitFlow");
const trackFlow = document.getElementById("trackFlow");

modeSubmitBtn.addEventListener("click", () => {
    modeSubmitBtn.classList.add("active");
    modeTrackBtn.classList.remove("active");
    submitFlow.classList.remove("hidden");
    trackFlow.classList.add("hidden");
});

modeTrackBtn.addEventListener("click", () => {
    modeTrackBtn.classList.add("active");
    modeSubmitBtn.classList.remove("active");
    trackFlow.classList.remove("hidden");
    submitFlow.classList.add("hidden");
});


// ======================================================
// TRACK YOUR REFUND
// ======================================================

const trackForm = document.getElementById("trackForm");
const trackSubmitBtn = document.getElementById("trackSubmitBtn");
const trackResult = document.getElementById("trackResult");
const trackMessage = document.getElementById("trackMessage");

const trackRefLabel = document.getElementById("trackRefLabel");
const trackStatusPill = document.getElementById("trackStatusPill");
const trackTimeline = document.getElementById("trackTimeline");
const trackTickets = document.getElementById("trackTickets");
const trackAttachments = document.getElementById("trackAttachments");

const trackUploadForm = document.getElementById("trackUploadForm");
const trackUploadBtn = document.getElementById("trackUploadBtn");
const trackUploadMessage = document.getElementById("trackUploadMessage");
const trackUploadFiles = document.getElementById("track_upload_files");
const trackUploadType = document.getElementById("track_upload_type");

// Kept from the last successful lookup so the upload form knows which
// refund + email to attach documents to without asking the user again.
let currentTrackReference = null;
let currentTrackEmail = null;

function formatDateTime(value) {
    if (!value) return "";
    return new Date(value).toLocaleString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function statusPillClass(status) {
    if (status === "REFUND_COMPLETED") return "is-complete";
    if (status === "REJECTED" || status === "CANCELLED") return "is-rejected";
    return "";
}

function formatLabel(value) {
    if (!value) return "—";
    return value
        .toString()
        .toLowerCase()
        .replace(/_/g, " ")
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

trackForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    trackSubmitBtn.disabled = true;
    trackSubmitBtn.textContent = "Checking...";
    trackMessage.innerHTML = "";
    trackResult.classList.add("hidden");

    const reference = document.getElementById("track_reference").value.trim();
    const email = document.getElementById("track_email").value.trim();

    try {
        const response = await fetch("http://127.0.0.1:8000/api/v1/refunds/lookup", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({ reference, email }),
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            trackMessage.innerHTML = `<p class="error">${result.message || "Refund not found."}</p>`;
            return;
        }

        const refund = result.refund;

        currentTrackReference = refund.reference;
        currentTrackEmail = email;

        trackRefLabel.textContent = refund.reference;

        trackStatusPill.textContent = refund.status_label;
        trackStatusPill.className = "track-status-pill " + statusPillClass(refund.status);

        trackTimeline.innerHTML = refund.timeline
            .map(
                (step) => `
                <li>
                    <span class="tl-label">${formatLabel(step.status)}</span>
                    <span class="tl-time">${formatDateTime(step.at)}</span>
                </li>
            `
            )
            .join("");

        trackTickets.innerHTML = refund.tickets
            .map(
                (t) => `
                <div class="ticket-mini">
                    <strong>${t.flight_number || "—"}</strong> · ${t.route || "—"}<br>
                    Ticket ${t.ticket_number || "—"} · Booking ref ${t.booking_reference || "—"}
                </div>
            `
            )
            .join("") || `<p class="track-mini-empty">No tickets on this request.</p>`;

        trackAttachments.innerHTML = refund.attachments.length
            ? refund.attachments
                  .map((a) => `<span class="attachment-chip">${a.name}</span>`)
                  .join("")
            : `<p class="track-mini-empty">No documents uploaded yet.</p>`;

        trackResult.classList.remove("hidden");
    } catch (error) {
        console.error(error);
        trackMessage.innerHTML = `<p class="error">Unable to connect to the server.</p>`;
    } finally {
        trackSubmitBtn.disabled = false;
        trackSubmitBtn.textContent = "Check Status";
    }
});


// ======================================================
// ADD MORE DOCUMENTS
// ======================================================

trackUploadForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    if (!currentTrackReference || !currentTrackEmail) {
        trackUploadMessage.innerHTML = `<p class="error">Look up your refund first.</p>`;
        return;
    }

    if (!trackUploadFiles.files.length) {
        trackUploadMessage.innerHTML = `<p class="error">Choose at least one file.</p>`;
        return;
    }

    trackUploadBtn.disabled = true;
    trackUploadBtn.textContent = "Uploading...";
    trackUploadMessage.innerHTML = "";

    try {
        const formData = new FormData();
        formData.append("reference", currentTrackReference);
        formData.append("email", currentTrackEmail);

        Array.from(trackUploadFiles.files).forEach((file) => {
            formData.append("attachments[]", file);
            formData.append("attachment_types[]", trackUploadType.value);
        });

        const response = await fetch("http://127.0.0.1:8000/api/v1/refunds/lookup/attachments", {
            method: "POST",
            headers: {
                Accept: "application/json",
            },
            body: formData,
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            trackUploadMessage.innerHTML = `<p class="error">${result.message || "Upload failed."}</p>`;
            return;
        }

        trackUploadMessage.innerHTML = `<p class="success">Document(s) uploaded successfully.</p>`;
        trackUploadForm.reset();

        // Refresh the attachments list to show the newly added file(s).
        trackForm.requestSubmit();
    } catch (error) {
        console.error(error);
        trackUploadMessage.innerHTML = `<p class="error">Unable to connect to the server.</p>`;
    } finally {
        trackUploadBtn.disabled = false;
        trackUploadBtn.textContent = "Upload Document(s)";
    }
});