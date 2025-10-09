function showToast(message, type = "error") {
  const colors = {
    error: "#FF4D4D", // Red for errors
    success: "#28A745", // Green for success
  };

  Toastify({
    text: message,
    duration: 5000,
    close: true,
    gravity: "top", // Toast position: top or bottom
    position: "right", // Toast alignment: left, center, or right
    stopOnFocus: true, // Pause on hover
    style: {
      background: colors[type] || colors.error, // use style.background per Toastify deprecation notice
      borderRadius: "12px",
      boxShadow: "0px 8px 15px rgba(0, 0, 0, 0.2)",
      padding: "20px",
      fontSize: "14px",
      fontWeight: "600",
      color: "#FFFFFF",
      textAlign: "center",
      lineHeight: "1.5",
    },
    offset: {
      x: 20, // Horizontal offset
      y: 20, // Vertical offset
    },
    className: "custom-toast",
  }).showToast();
}
