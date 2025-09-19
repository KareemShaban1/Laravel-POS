import React, { Component } from "react";
import { createRoot } from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            categories: [],
            barcode: "",
            search: "",
            customer_id: "",
            customer_name: "",
            vat_rate: 0,
            discount_amount: 0,
            selectedCategoryId: "",
            translations: {},
            loading: false,
            productCache: {}, // Cache for products by category
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.setCustomerName = this.setCustomerName.bind(this);
        this.setVatRate = this.setVatRate.bind(this);
        this.setDiscountAmount = this.setDiscountAmount.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
        this.setSelectedCategory = this.setSelectedCategory.bind(this);
        this.setSelectedCategoryById = this.setSelectedCategoryById.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        this.loadProducts();
        this.loadCustomers();
        this.loadCategories();
    }

    // load the transaltions for the react component
    loadTranslations() {
        axios
            .get("/admin/locale/cart")
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
                console.log(translations);
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
            });
    }

    loadCustomers() {
        axios.get(`/admin/customers`).then((res) => {
            const customers = res.data;
            this.setState({ customers });
        });
    }

    loadCategories() {
        axios.get(`/admin/categories-front`).then((res) => {
            const categories = res.data;
            this.setState({ categories });
        });
    }

    loadProducts(search = "", paginate = false, page = 1, categoryId = "") {
        // Create cache key
        const cacheKey = `${categoryId}_${search}`;

        // Check if we have cached data and no search term
        if (!search && this.state.productCache[cacheKey]) {
            this.setState({ products: this.state.productCache[cacheKey] });
            return;
        }

        // Set loading state
        this.setState({ loading: true });

        const params = new URLSearchParams();

        if (search) params.append("search", search);
        if (paginate) params.append("paginate", true);
        if (paginate) params.append("page", page); // Laravel pagination uses ?page=2
        if (categoryId) params.append("category_id", categoryId);

        axios
            .get(`/admin/cart-products?${params.toString()}`)
            .then((res) => {
                const products = res.data.data;

                // Cache the results if no search term
                if (!search) {
                    this.setState((prevState) => ({
                        productCache: {
                            ...prevState.productCache,
                            [cacheKey]: products,
                        },
                    }));
                }

                this.setState({ products, loading: false });
            })
            .catch((error) => {
                console.error("Error loading products:", error);
                this.setState({ loading: false });
            });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        console.log(barcode);
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/cart").then((res) => {
            console.log(res.data);
            const cart = res.data;
            this.setState({ cart });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode } = this.state;
        if (!!barcode) {
            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }
    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart });
        if (!qty) return;

        axios
            .post("/admin/cart/change-qty", { product_id, quantity: qty })
            .then((res) => {})
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getSubtotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * c.price);
        return sum(total);
    }

    getTotal(cart) {
        const subtotal = this.getSubtotal(cart);
        const vatAmount = (subtotal * this.state.vat_rate) / 100;
        const discountAmount = this.state.discount_amount;
        const total = subtotal + vatAmount - discountAmount;
        return Math.max(0, total).toFixed(2);
    }

    getVatAmount(cart) {
        const subtotal = this.getSubtotal(cart);
        return ((subtotal * this.state.vat_rate) / 100).toFixed(2);
    }
    handleClickDelete(product_id) {
        axios
            .post("/admin/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            });
    }
    handleEmptyCart() {
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        });
    }
    typingTimeout = null;

    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });

        clearTimeout(this.typingTimeout);

        this.typingTimeout = setTimeout(() => {
            if (search.length >= 2) {
                this.loadProducts(
                    search,
                    false,
                    1,
                    this.state.selectedCategoryId
                );
            } else if (search.length === 0) {
                this.loadProducts("", false, 1, this.state.selectedCategoryId); // رجّع كل المنتجات لو مفيش بحث
            }
        }, 300); // delay عشان ميعملش API call على كل key
    }

    handleSearch(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(barcode) {
        let product = this.state.products.find((p) => p.barcode === barcode);
        if (!!product) {
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                // update quantity
                this.setState({
                    cart: this.state.cart.map((c) => {
                        if (
                            c.id === product.id &&
                            product.quantity > c.pivot.quantity
                        ) {
                            c.pivot.quantity = c.pivot.quantity + 1;
                        }
                        return c;
                    }),
                });
            } else {
                if (product.quantity > 0 || !product.has_quantity) {
                    product = {
                        ...product,
                        pivot: {
                            quantity: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };

                    this.setState({ cart: [...this.state.cart, product] });
                }
            }

            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    // this.loadCart();
                    console.log(res);
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }

    setCustomerName(event) {
        this.setState({ customer_name: event.target.value });
    }

    setVatRate(event) {
        this.setState({ vat_rate: parseFloat(event.target.value) || 0 });
    }

    setDiscountAmount(event) {
        this.setState({ discount_amount: parseFloat(event.target.value) || 0 });
    }

    setSelectedCategory(event) {
        const categoryId = event.target.value;
        this.setState({ selectedCategoryId: categoryId });
        this.loadProducts(this.state.search, false, 1, categoryId);
    }

    setSelectedCategoryById(categoryId) {
        // Prevent multiple rapid clicks
        if (this.state.loading) {
            return;
        }

        this.setState({ selectedCategoryId: categoryId });
        this.loadProducts(this.state.search, false, 1, categoryId);
    }
    handleClickSubmit() {
        Swal.fire({
            title: this.state.translations["received_amount"],
            input: "text",
            inputValue: this.getTotal(this.state.cart),
            cancelButtonText: this.state.translations["cancel_pay"],
            showCancelButton: true,
            confirmButtonText: this.state.translations["confirm_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                return axios
                    .post("/admin/orders", {
                        customer_id: this.state.customer_id,
                        customer_name: this.state.customer_name,
                        vat_rate: this.state.vat_rate,
                        discount_amount: this.state.discount_amount,
                        subtotal: this.getSubtotal(this.state.cart),
                        vat_amount: this.getVatAmount(this.state.cart),
                        total: this.getTotal(this.state.cart),
                        amount,
                    })
                    .then((res) => {
                        this.loadCart();
                        Swal.fire(
                            "Success!",
                            "Order created successfully",
                            "success"
                        );
                        this.loadProducts();
                        // clear vat rate and discount amount and customer name
                        this.setState({
                            vat_rate: 0,
                            discount_amount: 0,
                            customer_name: "",
                        });
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err.response.data.message);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                //
            }
        });
    }
    render() {
        const { cart, products, customers, categories, barcode, translations } =
            this.state;
        return (
            <div className="row">
                <div className="col-md-6 col-lg-4">
                    <div className="row mb-2">
                        <div className="col">
                            <form onSubmit={this.handleScanBarcode}>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder={translations["scan_barcode"]}
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>
                        <div className="col">
                            <input
                                type="text"
                                className="form-control"
                                placeholder={
                                    translations["Customer Name"] ||
                                    "Customer Name"
                                }
                                value={this.state.customer_name}
                                onChange={this.setCustomerName}
                            />
                        </div>
                    </div>
                    <div className="user-cart">
                        <div
                            className="card"
                            style={{ minHeight: "300px", overflowY: "auto" }}
                        >
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{translations["product_name"]}</th>
                                        <th>{translations["quantity"]}</th>
                                        <th className="text-right">
                                            {translations["price"]}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cart.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.name}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    className="form-control form-control-sm qty"
                                                    value={c.pivot.quantity}
                                                    onChange={(event) =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            event.target.value
                                                        )
                                                    }
                                                />
                                                <button
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() =>
                                                        this.handleClickDelete(
                                                            c.id
                                                        )
                                                    }
                                                >
                                                    <i className="fas fa-trash"></i>
                                                </button>
                                            </td>
                                            <td className="text-right">
                                                {window.APP.currency_symbol}{" "}
                                                {(
                                                    c.price * c.pivot.quantity
                                                ).toFixed(2)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="row mb-2">
                        <div className="col-6">
                            <label>{translations["VAT Rate"]} (%):</label>
                            <input
                                type="number"
                                className="form-control form-control-sm"
                                value={this.state.vat_rate}
                                onChange={this.setVatRate}
                                min="0"
                                max="100"
                                step="0.01"
                            />
                        </div>
                        <div className="col-6">
                            <label>{translations["Discount Amount"]}:</label>
                            <input
                                type="number"
                                className="form-control form-control-sm"
                                value={this.state.discount_amount}
                                onChange={this.setDiscountAmount}
                                min="0"
                                step="0.01"
                            />
                        </div>
                    </div>

                    <div className="row">
                        <div className="col">
                            {translations["subtotal"] || "Subtotal"}:
                        </div>
                        <div className="col text-right">
                            {window.APP.currency_symbol}{" "}
                            {this.getSubtotal(cart).toFixed(2)}
                        </div>
                    </div>

                    {this.state.vat_rate > 0 && (
                        <div className="row">
                            <div className="col">
                                VAT ({this.state.vat_rate}%):
                            </div>
                            <div className="col text-right">
                                {window.APP.currency_symbol}{" "}
                                {this.getVatAmount(cart)}
                            </div>
                        </div>
                    )}

                    {this.state.discount_amount > 0 && (
                        <div className="row">
                            <div className="col">Discount:</div>
                            <div className="col text-right">
                                -{window.APP.currency_symbol}{" "}
                                {this.state.discount_amount.toFixed(2)}
                            </div>
                        </div>
                    )}

                    <div className="row">
                        <div className="col">
                            <strong>{translations["total"]}:</strong>
                        </div>
                        <div className="col text-right">
                            <strong>
                                {window.APP.currency_symbol}{" "}
                                {this.getTotal(cart)}
                            </strong>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-danger btn-block"
                                onClick={this.handleEmptyCart}
                                disabled={!cart.length}
                            >
                                {translations["cancel"]}
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSubmit}
                            >
                                {translations["checkout"]}
                            </button>
                        </div>
                    </div>
                </div>
                <div className="col-md-6 col-lg-8">
                    <div className="mb-2">
                        <input
                            type="text"
                            className="form-control"
                            placeholder={translations["search_product"] + "..."}
                            onChange={this.handleChangeSearch.bind(this)}
                        />
                    </div>

                    {/* Category Tabs */}
                    <div className="mb-3">
                        <ul
                            className="nav nav-tabs"
                            id="cartCategoryTabs"
                            role="tablist"
                        >
                            <li className="nav-item" role="presentation">
                                <button
                                    className={`nav-link ${
                                        this.state.selectedCategoryId === ""
                                            ? "active"
                                            : ""
                                    }`}
                                    onClick={() =>
                                        this.setSelectedCategoryById("")
                                    }
                                    type="button"
                                    disabled={this.state.loading}
                                >
                                    {translations["All_Products"] ||
                                        "All Products"}
                                </button>
                            </li>
                            {categories.map((category) => (
                                <li
                                    key={category.id}
                                    className="nav-item"
                                    role="presentation"
                                >
                                    <button
                                        className={`nav-link ${
                                            this.state.selectedCategoryId ===
                                            category.id.toString()
                                                ? "active"
                                                : ""
                                        }`}
                                        onClick={() =>
                                            this.setSelectedCategoryById(
                                                category.id.toString()
                                            )
                                        }
                                        type="button"
                                        disabled={this.state.loading}
                                    >
                                        {category.name}
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="order-product">
                        {this.state.loading ? (
                            <div className="text-center p-4">
                                <div
                                    className="spinner-border text-primary"
                                    role="status"
                                >
                                    <span className="sr-only">Loading...</span>
                                </div>
                                <p className="mt-2">Loading products...</p>
                            </div>
                        ) : (
                            products.map((p) => (
                                <div
                                    onClick={() =>
                                        this.addProductToCart(p.barcode)
                                    }
                                    key={p.id}
                                    className="item"
                                >
                                    <img src={p.image_url} alt="" />
                                    <h5 className="mt-2">
                                        {p.price} {window.APP.currency_symbol}
                                    </h5>
                                    <h5
                                        style={
                                            window.APP.warning_quantity >
                                            p.quantity
                                                ? { color: "red" }
                                                : {}
                                        }
                                    >
                                        {p.name} ({p.quantity})
                                    </h5>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

const root = document.getElementById("cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
