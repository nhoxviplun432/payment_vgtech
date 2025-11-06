jQuery(document).ready(function($) {
    // Override the showUI function to add custom behavior
    const originalShowUI = window.showUI;
    window.showUI = function(content, message, icon) {
        originalShowUI(content, message, icon);

        if (payos_checkout_data.status === "PAID") {
            // Add AJAX call to fetch the download link
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_download_link_tracuu',
                    order_id: payos_checkout_data.order_id
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Redirect to the PDF URL
                        window.location.href = response.data;
                    } else if(response.error && response.data){ 
                        const errorMessage = document.createElement('p');
                        errorMessage.textContent = response.error;
                        errorMessage.style.color = '#D32F2F';
                        content.appendChild(errorMessage);
                        window.location.href = response.data;
                    }else{
                        const errorMessage = document.createElement('p');
                        errorMessage.textContent = 'Có lỗi xảy ra, vui lòng thử lại sau';
                        errorMessage.style.color = '#D32F2F';
                        content.appendChild(errorMessage);
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr, status, error);
                }
                
            });
        }
    };
});