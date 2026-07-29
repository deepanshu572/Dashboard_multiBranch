const checkAuthAndAccess = () => {
    let role = localStorage.getItem('admin_role');
    
    // Hide Activity Log for restricted staff
    if (role !== 'admin') {
        $("#activityLogSection").hide();
    } else {
        loadActivityLogs();
    }
}

const loadActivityLogs = () => {
    $.ajax({
        url: apiurl,
        type: 'POST',
        data: { type: 'loadActivityLogs' },
        success: function(response) {
            try {
                let data = JSON.parse(response);
                let html = '';
                
                if (data.length > 0) {
                    data.forEach(log => {
                        let fullDetails = 'N/A';
                        let truncatedDetails = 'N/A';
                        let isLong = false;
                        
                        // Enhanced JSON details parsing for human readability
                        try {
                            if (log.details) {
                                let parsedDetails = JSON.parse(log.details);
                                if (parsedDetails) {
                                    const ignoreKeys = ['type', 'log_admin_username', 'product_id', 'p_id', 'id', 'b_id', 'cid', 'scid', 'vid', 'cat_id', 'brand_id', 'productImage', 'subCategoryImage', 'categoryImage', 'base64Image', 'identity_image', 'imageExtension', 'imageExtension2', 'fileExtension', 'image_path', 'product_path', 'logo_path', 'brandLogo', 'brandProductImage', 'productPhoto', 'imageFiles', 'detailsPayload', 'p_limit', 'under_category', 'under_subcategory', 'brand_name', 'review_val', 'review_nop', 'sku_number', 'selling_price', 'purchase_price', 'keyword', 'license_image', 'product_image', 'typeStatus', 'statusText'];
                                    let itemsForTruncation = [];

                                    if (log.action_type.toLowerCase().includes('delete') && parsedDetails.previous) {
                                        // DELETE VIEW
                                        let delParts = [];
                                        const oldData = parsedDetails.previous;
                                        const contextKeys = ['name', 'productName', 'title', 'brand_name', 'brandName', 'code', 'category_name'];
                                        
                                        for (let key in oldData) {
                                            if (!ignoreKeys.includes(key) && oldData[key]) {
                                                let cleanKey = key.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').replace(/^./, s => s.toUpperCase()).trim();
                                                let val = oldData[key];
                                                if (contextKeys.includes(key)) {
                                                    delParts.push(`<div class="detail-item"><b class="detail-label" style="color:#b91c1c;">${cleanKey}:</b> <span class="detail-val" style="font-weight:bold;">${val}</span></div>`);
                                                    itemsForTruncation.push(`${cleanKey}: ${val}`);
                                                }
                                            }
                                        }
                                        fullDetails = delParts.length > 0 ? delParts.join('') : '<i>(Record details capturer)</i>';
                                    } else if (parsedDetails.new && parsedDetails.previous) {
                                        // DIFF VIEW: show "Old -> New"
                                        let diffParts = [];
                                        const newData = parsedDetails.new;
                                        const oldData = parsedDetails.previous;
                                        
                                        for (let key in newData) {
                                            if (!ignoreKeys.includes(key) && newData[key] !== undefined) {
                                                let oldVal = (oldData[key] !== undefined && oldData[key] !== null) ? oldData[key] : 'N/A';
                                                let newVal = newData[key];
                                                
                                                if (String(oldVal) !== String(newVal)) {
                                                    let cleanKey = key.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').replace(/^./, s => s.toUpperCase()).trim();
                                                    diffParts.push(`<div class="detail-item"><b class="detail-label">${cleanKey}:</b> <span style="color: #ef4444; font-size: 11px; text-decoration: line-through;">${oldVal}</span> <span style="color: #10b981;">-> ${newVal}</span></div>`);
                                                    itemsForTruncation.push(`${cleanKey}: ${oldVal}->${newVal}`);
                                                }
                                            }
                                        }
                                        fullDetails = diffParts.length > 0 ? diffParts.join('') : '<i style="color:#9ca3af;">(Metadata changes)</i>';
                                    } else {
                                        // FLAT VIEW: original behavior
                                        let flatParts = [];
                                        for (let key in parsedDetails) {
                                            if (!ignoreKeys.includes(key) && (parsedDetails[key] !== '' && parsedDetails[key] !== null)) {
                                                if (key === 'cartData' && typeof parsedDetails[key] === 'string') {
                                                    try {
                                                        let cartParsed = JSON.parse(parsedDetails[key]);
                                                        let items = cartParsed.map(item => `${item.qty || item.nop}x ${item.name}`).join(', ');
                                                        flatParts.push(`<div class="detail-item"><b class="detail-label">Items:</b> <span class="detail-val">${items}</span></div>`);
                                                        itemsForTruncation.push(`Items: ${items}`);
                                                    } catch (ce) {}
                                                } else {
                                                    let cleanKey = key.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').replace(/^./, str => str.toUpperCase()).trim();
                                                    let valStr = String(parsedDetails[key]);
                                                    flatParts.push(`<div class="detail-item"><b class="detail-label">${cleanKey}:</b> <span class="detail-val">${valStr}</span></div>`);
                                                    itemsForTruncation.push(`${cleanKey}: ${valStr}`);
                                                }
                                            }
                                        }
                                        fullDetails = flatParts.length > 0 ? flatParts.join('') : 'N/A';
                                    }

                                    // Create truncated view for the table cell
                                    let simpleJoined = itemsForTruncation.join(', ');
                                    if (simpleJoined.length > 60) {
                                        truncatedDetails = simpleJoined.substring(0, 57) + '...';
                                        isLong = true;
                                    } else {
                                        truncatedDetails = simpleJoined || 'N/A';
                                        if (itemsForTruncation.length > 1) isLong = true;
                                    }
                                }
                            }
                        } catch(e) {
                            fullDetails = log.details || 'N/A';
                            if (fullDetails.length > 60) {
                                truncatedDetails = fullDetails.substring(0, 57) + '...';
                                isLong = true;
                            } else {
                                truncatedDetails = fullDetails;
                            }
                        }

                        let detailDisplay = isLong ? `
                            <div class="detail-container">
                                <span class="detail-truncated">${truncatedDetails}</span>
                                <span class="detail-full">${fullDetails}</span>
                                <span class="see-more-btn" onclick="toggleDetails(this)">See More</span>
                            </div>
                        ` : `<span>${truncatedDetails}</span>`;

                        let formattedDate = log.timestamp;
                        try {
                            let safeDateString = log.timestamp.replace(/-/g, 'https://indiantechsolution.com/');
                            let dateObj = new Date(safeDateString);
                            if (!isNaN(dateObj)) {
                                formattedDate = dateObj.toLocaleString('en-US', {
                                    month: 'short', 
                                    day: 'numeric', 
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                });
                            }
                        } catch(e) {}

                        html += `
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px; font-weight: 600; color: #4F46E5;">@${log.staff_username}</td>
                            <td style="padding: 12px; color: #333;"><span style="background:#eef2ff; color:#4338ca; padding:4px 8px; border-radius:4px; font-size:12px;">${log.action_type}</span></td>
                            <td style="padding: 12px; color: #666; font-size: 13px;">${formattedDate}</td>
                            <td style="padding: 12px; color: #555; font-size: 13px; line-height: 1.4;">${detailDisplay}</td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No recent activity</td></tr>';
                }
                
                $("#activityLogTableBody").html(html);
                
            } catch(e) {
                console.error("Error parsing logs", e);
                $("#activityLogTableBody").html('<tr><td colspan="4" style="text-align:center; padding: 20px;">Error loading logs</td></tr>');
            }
        }
    });
}

// Check on load
checkAuthAndAccess();

const loadAllData = async () => {
    const myFormData = new FormData();
    myFormData.append('type', 'loadAllData');
    const response = await fetch(apiurl, {
        method: 'POST',
        body: myFormData
    });
    const data = await response.json();
  
    console.log(data);

    $(".userCount").html(data.users ? data.users.length : 0);
    $(".orderCount").html(data.orders ? data.orders.length : 0);
    const posOrderCount = data.pos_orders ? data.pos_orders.length : 0;
    $(".posProductCount").html(posOrderCount);
    $(".productCount").html(data.products ? data.products.length : 0);
}


const loadRecentOrders = async () => {

    

fetch(apiurl, {
  method: "POST",
  headers: {
    "Content-Type": "application/x-www-form-urlencoded"
  },
  body: "type=getRecentOrders"
})
.then(res => res.json())
.then(data => {

  let html = "";

  data.forEach(order => {
    html += `
      <div class="shop_model_order_item">
        <div>
          <div class="shop_model_order_id">Order #${order.id}</div>
          <div class="shop_model_order_time">
            ${order.dor} 
          </div>
        </div>
        <h2 class="shop_model_order_amount">₹${order.total}</h2>
        <span class="shop_model_status ${order.status}">
          ${order.status}
        </span>
      </div>
    `;
  });

  document.getElementById("shop_model_recent_orders").innerHTML = html;
});


}

// Global function to toggle activity details
window.toggleDetails = function(btn) {
    const container = $(btn).closest('.detail-container');
    container.toggleClass('expanded');
    
    if (container.hasClass('expanded')) {
        $(btn).text('See Less');
    } else {
        $(btn).text('See More');
    }
};