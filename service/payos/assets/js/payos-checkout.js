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
content.style.height = isMobileScreen() ? "680px" : "360px";
content.style.maxHeight = isMobileScreen() ? "680px" : "360px";
content.style.width = "100%";

const beforeUnloadHandler = (e) => {
  e.preventDefault;
  e.returnValue = true;
};
window.addEventListener("beforeunload", beforeUnloadHandler);
// setTimeout(() => {
//   content.scrollIntoView({ behavior: "smooth" });
// }, 500);

const isJsonString = (str) => {
  if (!str) {
    return false;
  }
  try {
    JSON.parse(str);
    return true;
  } catch (e) {
    return false;
  }
};

const showUI = (content, message, icon, isError = false, checkPaid = false) => {
  const color = isError ? "#D32F2F" : "#6655ff";
  let downloadButtonHTML = "";

  if (checkPaid) {
    const currentUrl = window.location.href;
    let orderId = null;
    if (currentUrl.includes('order-received/')) {
      const parts = currentUrl.split('order-received/');
      if (parts[1]) {
        orderId = parts[1].split('/')[0];
      }
    }

    $.post(payos_checkout_data.ajax_url, {
      action: 'handle_checkPaymentPaid',
      order_id: orderId,
    }, function (response) {
      if (response.success === true) {
        if (response.data.type_paid === 'tracuu') {
          downloadButtonHTML = response.data.success;
        }
        content.innerHTML = `
          <div style="display:flex; flex-direction: column; justify-content: center; align-items: center; height: 100%">
            <div style="text-align: center; padding: 20px;">
              <p style="color:${color}; font-size:20px">${message}</p>
              <img src="${icon}" style="width: 50px; height: 50px; margin-bottom: 10px"/>
              ${downloadButtonHTML}
            </div>
          </div>`;
      } else {
        console.error('Có lỗi xảy ra:', response.data);
      }
    }).fail(function () {
      console.error('Không thể kết nối tới server');
    });
  } else {
    content.innerHTML = `
      <div style="display:flex; flex-direction: column; justify-content: center; align-items: center; height: 100%">
        <div style="text-align: center; padding: 20px;">
          <p style="color:${color}; font-size:20px">${message}</p>
          <img src="${icon}" style="width: 50px; height: 50px; margin-bottom: 10px"/>
        </div>
      </div>`;
  }
};

switch (payos_checkout_data.status) {
  case "PENDING":
    let paymentLinkOrigin = null;
    const redirectUrl = payos_checkout_data.redirect_url;
    const checkoutUrl = payos_checkout_data.checkout_url;
    const paymentLinkDialogUrl = checkoutUrl + "?redirect_uri=" + encodeURIComponent(redirectUrl);
    try {
      paymentLinkOrigin = payos_checkout_data.checkout_url;

      const handlePostMessage = async (event) => {
        if (event.origin !== new URL(paymentLinkOrigin).origin) {
          return;
        }
        const eventData = isJsonString(event.data) ? JSON.parse(event.data) : undefined;
        if (!eventData) {
          return;
        }
        if (eventData.type !== "payment_response") return;
        const responseData = eventData.data;
        if (responseData?.status === "PAID") {
          const notice = document.querySelector('.woocommerce-notice--success.woocommerce-thankyou-order-received');
          if (notice) {
            notice.remove();
          }// Hiển thị nút download PDF
          const getQR = document.getElementById('get-qr-wrap');
          if(getQR){
            getQR.remove();
          }
          window.removeEventListener("beforeunload", beforeUnloadHandler);
          
          showUI(content, payos_checkout_data.message, payos_checkout_data.icon, false, true);
        }
        // showUI(content, payos_checkout_data.message, payos_checkout_data.icon, false, true, true);
      };
      content.innerHTML = `<iframe id="payos-checkout" src=${paymentLinkDialogUrl} allow="clipboard-read; clipboard-write"></iframe>`;
      window.addEventListener("message", handlePostMessage);
    } catch (error) {
      showUI(content, payos_checkout_data.error_message, payos_checkout_data.icon, true);
      window.removeEventListener("beforeunload", beforeUnloadHandler);
    }
    break;
  case "PAID":
    showUI(content, payos_checkout_data.message, payos_checkout_data.icon, false, true);
    const notice = document.querySelector('.woocommerce-notice--success.woocommerce-thankyou-order-received');
    if (notice) {
      notice.remove();
    }
    const getQR = document.getElementById('get-qr-wrap');
    if(getQR){
      getQR.remove();
    }
    break;
  case "ERROR":
    showUI(content, payos_checkout_data.message, payos_checkout_data.icon, true);
    break;
  default:
    content.innerHTML = `
    <div id=payos-checkout-loading-container>
      <div id=payos-checkout-loading>
      <div id="loader-payos"></div>
      <p>${payos_checkout_data.message}</p>
      </div>
    </div>`;
}