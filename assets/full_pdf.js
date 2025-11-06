// jQuery(document).ready(function($) {
//     $('#download-pdf-full').on('click', function(e) {
//         e.preventDefault();
//         const url_order = window.location.href;
//         validateOrder(url_order); 
//     });
// });
jQuery(document).on('click', '#download-pdf-full', function(e) {
    e.preventDefault(); 
    const url_order = window.location.href; 
    validateOrder(url_order); 
});


$(document).ready(function() {
    if($('.woocommerce-order').leghth > 0) {
        $(this).addClass('is-order-pdf');
    }
    $('#capture-iframe').click(function() {
        // Lấy URL của iframe
        var iframe = document.getElementById('payos-checkout');
        var iframeUrl = iframe.src; // Lấy đường dẫn hiện tại của iframe

        // Sử dụng Clipboard API để copy đường dẫn vào clipboard
        navigator.clipboard.writeText(iframeUrl).then(function() {
            $('#capture-iframe').text('Đã copy đường dẫn QR!');
        }).catch(function(error) {
            alert('Có lỗi xảy ra!');
        });
    });
});

function validateOrder(url_order) {
    $('#download-pdf-full').text('Đang tạo file PDF Full...');
    
    if (!url_order) {
        window.location.reload();
    } else {
        const orderId = url_order.split('order-received/')[1]?.split('/')[0];

        if (orderId) {
            const data = {
                action: 'handle_get_full_pdf_api',
                order_id: orderId,
                _ajax_nonce: ajax_object.nonce // Gửi nonce tới server để bảo vệ yêu cầu
            };

            // Gọi AJAX
            $.post(ajax_object.ajax_url, data, function(response) {
                if (response) {
                    var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;

                    // Kiểm tra response có success và chứa URL PDF
                    if (parsedResponse.success && parsedResponse.data && parsedResponse.data.success === 'true') {
                        var pdfUrl = parsedResponse.data.data;

                        if (pdfUrl) {
                            window.open(pdfUrl, '_blank');
                            $('#text-tracuu-order-completed').text('Chúng tôi đã gửi file pdf đầy đủ nội dung đến email của bạn. Vui lòng kiểm tra tất cả hộp thư (Thư chính và thư spam). Trân trọng!');
                            $('#download-pdf-full').remove();
                        } else {
                            $('#download-pdf-full').html('Download PDF <i class="fas fa-download"></i>');
                            $('#download-pdf-full').after('<p class="error-message">Không tìm thấy URL của file PDF, vui lòng liên hệ với chúng tôi!</p>');
                        }
                    } else {
                        $('#download-pdf-full').html('Download PDF <i class="fas fa-download"></i>');
                        $('#download-pdf-full').after('<p class="error-message">Có lỗi xảy ra, vui lòng liên hệ với chúng tôi!</p>');
                    }
                } else {
                    $('#download-pdf-full').html('Download PDF <i class="fas fa-download"></i>');
                    $('#download-pdf-full').after('<p class="error-message">Có lỗi xảy ra, vui lòng liên hệ với chúng tôi!</p>');
                }
            }).fail(function() {
                $('#download-pdf-full').html('Download PDF <i class="fas fa-download"></i>');
                $('#download-pdf-full').after('<p class="error-message">Không thể kết nối tới server, vui lòng thử lại sau!</p>');
            });
        } else {
            $('#download-pdf-full').after('<p class="error-message">Không tìm thấy mã đơn hàng, vui lòng thử lại!</p>');
        }
    }
}
