<!-- File: partials/flash.php -->
<div class="container" id="flash">
    <?php foreach (get_flash_messages() as $msg): ?>
        <div class="row justify-content-center">
            <div class="alert alert-<?php echo htmlspecialchars($msg["color"] ?? "info"); ?>">
                <?php echo htmlspecialchars($msg["text"] ?? ""); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script>
(() => {
    // Display each message for 30000 milliseconds (30 seconds).
    const flash_message_delay = 30000;

    /**
     * Called by the interval to timestamp new messages and remove expired ones.
     * Includes messages added later by browser add_flash calls.
     */
    function process_flash_messages() {
        const now = Date.now();
        document.querySelectorAll("#flash .row").forEach((message) => {
            if (!message.dataset.removeAt) {
                message.dataset.removeAt = now + flash_message_delay;
            }
            if (Number(message.dataset.removeAt) <= now) {
                message.remove();
            }
        });
    }
    setInterval(process_flash_messages, 100);
})();
</script>
