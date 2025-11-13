const isMobileScreen = () => Boolean(
  navigator.userAgent.match(/Android/i) ||
  navigator.userAgent.match(/webOS/i) ||
  navigator.userAgent.match(/iPhone/i) ||
  navigator.userAgent.match(/iPad/i) ||
  navigator.userAgent.match(/iPod/i) ||
  navigator.userAgent.match(/BlackBerry/i) ||
  navigator.userAgent.match(/Windows Phone/i)
);

const content = document.getElementById("payos-checkout-container");
content.style.height = isMobileScreen() ? "620px" : "340px";
content.style.width = "100%";

const beforeUnloadHandler = (e) => {
  e.preventDefault;
  e.returnValue = true;
};
window.addEventListener("beforeunload", beforeUnloadHandler);

setTimeout(() => {
  content.scrollIntoView({ behavior: "smooth" });
}, 500);

const isJsonString = (str) => {
  if (!str) return false;
  try {
    JSON.parse(str);
    return true;
  } catch {
    return false;
  }
};

const showUI = (content, message, icon, isError = false) => {
  const color = isError ? "#D32F2F" : "#fff";
  content.innerHTML = `
    <div style="
      background-color:#00000075;
      display:flex;
      flex-direction:column;
      justify-content:center;
      align-items:center;
      height:100%;
      border:1px solid var(--e-global-color-primary);
      max-width:550px;
      margin:auto;
      border-radius:5px;
    ">
      <div style="text-align:center; padding:20px;">
        <img src="${icon}" style="width:50px; height:50px; margin-bottom:10px"/>
        <p style="color:${color}; font-size:20px">${message}</p>
        <div id="btn-home"></div>
      </div>
    </div>
  `;
};

console.log(payos_checkout_data.vgtech_payment);

switch (payos_checkout_data.status) {
  case "PENDING":
    let paymentLinkOrigin = null;
    const redirectUrl = payos_checkout_data.redirect_url;
    const checkoutUrl = payos_checkout_data.checkout_url;
    const paymentLinkDialogUrl = checkoutUrl + "?redirect_uri=" + encodeURIComponent(redirectUrl);

    try {
      paymentLinkOrigin = payos_checkout_data.checkout_url;

      const handlePostMessage = async (event) => {
        if (event.origin !== new URL(paymentLinkOrigin).origin) return;
        const eventData = isJsonString(event.data) ? JSON.parse(event.data) : undefined;
        if (!eventData || eventData.type !== "payment_response") return;

        const responseData = eventData.data;
        if (responseData && responseData.status === "PAID") {
          window.removeEventListener("beforeunload", beforeUnloadHandler);
          showUI(content, payos_checkout_data.message, payos_checkout_data.icon);

          if (payos_checkout_data.refresh_when_paid === 'yes') {
            setInterval(() => window.location.reload(), 5000);
          }
        }
      };

      content.innerHTML = `<iframe id="payos-checkout" src="${paymentLinkDialogUrl}" allow="clipboard-read; clipboard-write"></iframe>`;
      window.addEventListener("message", handlePostMessage);

    } catch (error) {
      showUI(content, payos_checkout_data.error_message, payos_checkout_data.icon, true);
      window.removeEventListener("beforeunload", beforeUnloadHandler);
    }
    break;

  case "PAID":
    showUI(content, payos_checkout_data.message, payos_checkout_data.icon);

    const url_order = window.location.href;
    const orderId = url_order.split('order-received/')[1]?.split('/')[0];

    if (orderId) {
      jQuery.ajax({
        url: ajax_object.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'vgtech_get_payment_status',
          nonce: ajax_object.nonce,
          order_id: orderId,
          status: 'PAID'
        },
        success: function (response) {
          console.log('✅ AJAX Payment Status:', response);
          if (response.message === 'success') {
            console.log(`🎯 +${response.total_added} lượt xem từ order #${response.order_id}`);
            console.log(response.button);
            // ✅ Nếu có URL trả về, hiển thị lại giao diện với nút
            // Nếu có URL button, append ngay sau khi render
              if (response.button) {
                console.log(response.button);
                const btnHome = document.querySelector('#btn-home');
                if (btnHome) {
                  const btn = document.createElement('a');
                  btn.href = response.button;
                  btn.innerText = 'Trang chủ';
                  btn.style.cssText = `
                    display:inline-block;
                    margin-top:15px;
                    color:var(--e-global-color-secondary);
                    background-color:transparent;
                    background-image:linear-gradient(180deg,#F3D197 14%,#70410F 59%);
                    font-size:15px;
                    padding:12px 24px;
                    border-radius:3px;
                    text-decoration:none;
                  `;
                  btnHome.appendChild(btn);
                }
              }

          } else {
            console.warn('⚠️', response.status);
          }
        },
        error: function (xhr, status, error) {
          console.error('❌ AJAX Error:', error);
        },
      });
    } else {
      console.warn('⚠️ Không tìm thấy order_id trong URL.');
    }
    break;

  case "ERROR":
    showUI(content, payos_checkout_data.message, payos_checkout_data.icon, true);
    break;

  default:
    content.innerHTML = `
      <div id="payos-checkout-loading-container">
        <div id="payos-checkout-loading">
          <div id="loader-payos"></div>
          <p>${payos_checkout_data.message}</p>
        </div>
      </div>`;
}
