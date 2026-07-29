let flashProducts = [];
let flashFilteredProducts = [];
let flashCategories = [];
let flashSubCategories = [];
const selectedFlashProductIds = new Set();

const FLASH_SALE_KEYS = ["flash_sale", "is_flash_sale", "in_flash_sale", "flashsale", "flashSale"];

$(document).ready(function () {
    bindFlashSaleEvents();
    initializeFlashSalePage();
});

function bindFlashSaleEvents() {
    $("#flash-category-filter").on("change", function () {
        renderFlashSubCategoryOptions($(this).val());
        filterFlashProducts();
    });

    $("#flash-subcategory-filter").on("change", filterFlashProducts);
    $("#flash-search").on("input", filterFlashProducts);
    $("#flash-selection-filter").on("change", filterFlashProducts);

    $("#flash-select-visible").on("click", selectVisibleFlashProducts);
    $("#flash-clear-selected").on("click", clearAllFlashSelections);

    $("#flash-add-selected").on("click", function () {
        handleFlashBatchAction(true);
    });

    $("#flash-remove-selected").on("click", function () {
        handleFlashBatchAction(false);
    });

    $("#flash-select-all-table").on("change", function () {
        const shouldSelect = this.checked;
        flashFilteredProducts.forEach((item) => {
            const id = getProductId(item);
            if (!id) return;
            if (shouldSelect) {
                selectedFlashProductIds.add(id);
            } else {
                selectedFlashProductIds.delete(id);
            }
        });

        refreshFlashListAfterSelectionChange();
    });
}

function initializeFlashSalePage() {
    loadFlashCategoryList();
    loadFlashSubCategoryList();
    loadFlashProductList();
}

function loadFlashCategoryList() {
    $.ajax({
        url: apiurl,
        type: "POST",
        data: { type: "loadCategory" },
        success: function (response) {
            if (response && response !== "error" && response !== "null") {
                flashCategories = JSON.parse(response);
            } else {
                flashCategories = [];
            }

            let html = '<option value="all">All Categories</option>';
            flashCategories.forEach((item) => {
                html += `<option value="${item.id}">${item.name}</option>`;
            });
            $("#flash-category-filter").html(html);
        },
        error: function () {
            errorAlert("Unable to load categories");
        },
    });
}

function loadFlashSubCategoryList() {
    $.ajax({
        url: apiurl,
        type: "POST",
        data: { type: "loadSubCategory" },
        success: function (response) {
            if (response && response !== "error" && response !== "null") {
                flashSubCategories = JSON.parse(response);
            } else {
                flashSubCategories = [];
            }

            renderFlashSubCategoryOptions("all");
        },
        error: function () {
            errorAlert("Unable to load subcategories");
        },
    });
}

function loadFlashProductList() {
    $.ajax({
        url: apiurl,
        type: "POST",
        data: { type: "loadProduct" },
        success: function (response) {
            if (response && response !== "error" && response !== "null") {
                flashProducts = JSON.parse(response);
            } else {
                flashProducts = [];
            }

            filterFlashProducts();
        },
        error: function () {
            errorAlert("Unable to load product list");
        },
    });
}

function renderFlashSubCategoryOptions(categoryId) {
    let html = '<option value="all">All Subcategories</option>';

    flashSubCategories
        .filter((item) => categoryId === "all" || item.under_category === categoryId)
        .forEach((item) => {
            html += `<option value="${item.id}">${item.name}</option>`;
        });

    $("#flash-subcategory-filter").html(html);
}

function filterFlashProducts() {
    const selectedCategory = $("#flash-category-filter").val();
    const selectedSubCategory = $("#flash-subcategory-filter").val();
    const selectionFilter = $("#flash-selection-filter").val() || "all";
    const searchText = $("#flash-search").val().toLowerCase().trim();

    flashFilteredProducts = flashProducts.filter((product) => {
        const productId = getProductId(product);
        const matchCategory = selectedCategory === "all" || product.under_category === selectedCategory;
        const matchSubCategory = selectedSubCategory === "all" || product.under_subcategory === selectedSubCategory;
        const matchSearch =
            !searchText ||
            (product.name || "").toLowerCase().includes(searchText) ||
            (product.sku_number || "").toLowerCase().includes(searchText);
        let matchSelection = true;
        if (selectionFilter === "selected") {
            matchSelection = productId && selectedFlashProductIds.has(productId);
        } else if (selectionFilter === "in_flash_sale") {
            matchSelection = getFlashSaleStatus(product);
        }

        return matchCategory && matchSubCategory && matchSearch && matchSelection;
    });

    renderFlashProducts();
}

function renderFlashProducts() {
    let html = "";

    flashFilteredProducts.forEach((item, index) => {
        const productId = getProductId(item);
        if (!productId) return;
        const isSelected = selectedFlashProductIds.has(productId);
        const inFlashSale = getFlashSaleStatus(item);

        html += `
            <tr>
                <td><input type="checkbox" class="flash-row-checkbox" data-id="${productId}" ${isSelected ? "checked" : ""}></td>
                <td>${index + 1}</td>
                <td><img src="${imgurl + item.image_path}" alt="${item.name}"></td>
                <td>${item.name || "-"}</td>
                <td>${getCategoryName(item.under_category)}</td>
                <td>${getSubCategoryName(item.under_subcategory)}</td>
                <td>${item.sku_number || "-"}</td>
                <td>${item.selling_price || "0"}</td>
                <td>${item.stock || "0"}</td>
                <td><span class="flash-status ${inFlashSale ? "on" : "off"}">${inFlashSale ? "Added" : "Not Added"}</span></td>
                <td>
                    <button type="button" class="flash-row-btn ${inFlashSale ? "remove" : "add"}" onclick="updateSingleFlashProduct('${productId}', ${!inFlashSale})">
                        ${inFlashSale ? "Remove" : "Add"}
                    </button>
                </td>
            </tr>
        `;
    });

    if (!html) {
        html = '<tr><td colspan="11" style="text-align:center;">No products found</td></tr>';
    }

    $("#flash-product-table tbody").html(html);
    $("#flash-total-data").text(flashFilteredProducts.length);

    bindFlashRowSelectionEvents();
    syncSelectAllCheckbox();
    updateSelectedCount();
}

function bindFlashRowSelectionEvents() {
    $(".flash-row-checkbox").off("change").on("change", function () {
        const id = normalizeFlashId($(this).attr("data-id"));

        if (this.checked) {
            selectedFlashProductIds.add(id);
        } else {
            selectedFlashProductIds.delete(id);
        }

        refreshFlashListAfterSelectionChange();
    });
}

function syncSelectAllCheckbox() {
    if (!flashFilteredProducts.length) {
        $("#flash-select-all-table").prop("checked", false);
        return;
    }

    const allVisibleSelected = flashFilteredProducts.every((item) => {
        const id = getProductId(item);
        return id ? selectedFlashProductIds.has(id) : false;
    });
    $("#flash-select-all-table").prop("checked", allVisibleSelected);
}

function selectVisibleFlashProducts() {
    flashFilteredProducts.forEach((item) => {
        const id = getProductId(item);
        if (id) {
            selectedFlashProductIds.add(id);
        }
    });
    refreshFlashListAfterSelectionChange();
}

function clearAllFlashSelections() {
    selectedFlashProductIds.clear();
    refreshFlashListAfterSelectionChange();
}

function updateSelectedCount() {
    reconcileSelectedIds();
    $("#flash-selected-count").text(`Selected: ${selectedFlashProductIds.size}`);
}

function getCategoryName(categoryId) {
    const found = flashCategories.find((item) => item.id === categoryId);
    return found ? found.name : "-";
}

function getSubCategoryName(subCategoryId) {
    const found = flashSubCategories.find((item) => item.id === subCategoryId);
    return found ? found.name : "-";
}

function getFlashSaleStatus(product) {
    return FLASH_SALE_KEYS.some((key) => product[key] === "true" || product[key] === true || product[key] === "1" || product[key] === 1);
}

function getProductId(product) {
    if (!product) return "";

    const keys = ["p_id", "id", "product_id", "pid", "productId"];
    for (let i = 0; i < keys.length; i++) {
        const value = normalizeFlashId(product[keys[i]]);
        if (value) return value;
    }

    return "";
}

function normalizeFlashId(value) {
    if (value === undefined || value === null) return "";
    return String(value).trim();
}

function reconcileSelectedIds() {
    const validIds = new Set(
        flashProducts
            .map((item) => getProductId(item))
            .filter(Boolean)
    );

    Array.from(selectedFlashProductIds).forEach((id) => {
        if (!validIds.has(id)) {
            selectedFlashProductIds.delete(id);
        }
    });
}

function refreshFlashListAfterSelectionChange() {
    if ($("#flash-selection-filter").val() === "selected") {
        filterFlashProducts();
    } else {
        renderFlashProducts();
    }
}

function setLocalFlashSaleStatus(pId, status) {
    const targetId = String(pId);
    flashProducts = flashProducts.map((item) => {
        if (getProductId(item) === targetId) {
            return {
                ...item,
                flash_sale: status ? "true" : "false",
            };
        }
        return item;
    });
}

function handleFlashBatchAction(shouldAdd) {
    const selectedIds = Array.from(selectedFlashProductIds);
    if (!selectedIds.length) {
        warningAlert("Please select at least one product");
        return;
    }

    const text = shouldAdd
        ? "You want to add selected products to Flash Sale"
        : "You want to remove selected products from Flash Sale";

    showConfirmationDialog(text).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        updateFlashProductsByIds(selectedIds, shouldAdd);
    });
}

function updateSingleFlashProduct(pId, shouldAdd) {
    showConfirmationDialog(shouldAdd ? "Add this product to Flash Sale?" : "Remove this product from Flash Sale?").then((confirmed) => {
        if (!confirmed) {
            return;
        }

        updateFlashProductsByIds([pId], shouldAdd);
    });
}

function updateFlashProductsByIds(productIds, shouldAdd) {
    const requests = productIds.map((id) => updateFlashStatusRequest(id, shouldAdd));

    Promise.all(requests).then((results) => {
        const successCount = results.filter(Boolean).length;
        const failCount = results.length - successCount;

        if (successCount > 0) {
            productIds.forEach((id) => {
                setLocalFlashSaleStatus(id, shouldAdd);
                selectedFlashProductIds.delete(id);
            });
            filterFlashProducts();
        }

        if (successCount > 0 && failCount === 0) {
            successAlert(shouldAdd ? "Selected products added to Flash Sale" : "Selected products removed from Flash Sale");
        } else if (successCount > 0 && failCount > 0) {
            warningAlert(`${successCount} updated, ${failCount} failed`);
        } else {
            errorAlert("Flash Sale update failed. Please verify the backend endpoint.");
        }
    });
}

function updateFlashStatusRequest(pId, shouldAdd) {
    const statusText = shouldAdd ? "true" : "false";

    const payloads = [
        { type: "updateProductTitle", typeStatus: "flash_sale", id: pId, statusText: statusText },
        { type: "updateProductTitle", typeStatus: "is_flash_sale", id: pId, statusText: statusText },
        { type: "updateFlashSaleProduct", id: pId, statusText: statusText },
    ];

    return tryPayloadsSequentially(payloads);
}

function tryPayloadsSequentially(payloads, index = 0) {
    if (index >= payloads.length) {
        return Promise.resolve(false);
    }

    return postFlashSaleUpdate(payloads[index]).then((ok) => {
        if (ok) {
            return true;
        }

        return tryPayloadsSequentially(payloads, index + 1);
    });
}

function postFlashSaleUpdate(data) {
    return new Promise((resolve) => {
        $.ajax({
            url: apiurl,
            type: "POST",
            data: data,
            success: function (response) {
                resolve(response !== "error" && response !== "null");
            },
            error: function () {
                resolve(false);
            },
        });
    });
}
