<?php
// session_start();
require_once './bootstrap.php';

use App\Services\ProductService;

$productService = new ProductService();
$products = $productService->products();

if (isset($_SESSION['token'])) {
    ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@3.0.0/dist/vue.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <title>Products</title>
</head>
<body>
<div class="container" id = "app">
    <div class="table-responsive" style="margin-top: 20px;">
        <h3 class="text-center">Products</h3>
        <div class="filter d-flex flex-columns mb-2">
            <div style="margin-right: 5px;">
                <label>Status</label>&nbsp;
                <select v-model="status" @change="getProducts()">
                    <option value="" selected>all</option>
                    <option value="sale">sale</option>
                    <option value="out">out</option>
                    <option value="hidden">hidden</option>
                    <option value="deleted">deleted</option>
                </select>
            </div>
            <div style="margin-right: 5px;">
                <label>PerPage</label>&nbsp;
                <select v-model="perPage" @change="getProducts()">
                    <option value="15" selected>15</option>
                    <option value="25">25</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                </select>
            </div>
            <div style="margin-right: 5px;">
                <label>Search</label>&nbsp;
                <input type="text" v-model="search" @keyup="getProducts()">
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">quantity</th>
                    <th scope="col">Status</th>
                    <th scope="col">#</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="data.length != 0" v-for="(product, index) in data" :key="product.id">
                    <td>{{ ++ index }}</td>
                    <td> <img :src="product.main_image" width="150" height="150"/></td>
                    <td>{{ product.name }}</td>
                    <td>{{ product.regular_price.amount }}</td>
                    <td>{{ product.quantity }}</td>
                    <td>{{ product.status }}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" style="margin-right: 2px;" @click="deleteProduct(product.id)">Delete</button>
                        <button type="button" class="btn btn-warning btn-sm" @click.prevent="editProduct(product)">Edit</button>
                    </td>
                </tr>
                <tr v-else>
                    <td colspan="7" class="text-center">No Data Found</td>
                </tr>
            </tbody>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
     </div>
    <div class="modal" tabindex="-1" role="dialog" id="editProductModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Edit Product</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeEditModal()">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div v-if="errors">
                <div v-for="(error, index) in errors" :key="index" class="alert alert-danger">{{ error }}</div>
            </div>
            <form>
                <div class="form-group mb-2">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" v-model="product.name">
                </div>
                <div class="form-group mb-2">
                    <label for="price">Price</label>
                    <input type="text" class="form-control" id="price" v-model="product.price">
                </div>
                <div class="form-group mb-2">
                    <label for="quantity">Quantity</label>
                    <input type="text" class="form-control" id="quantity" v-model="product.quantity">
                </div>
                <div class="form-group mb-2">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" v-model="product.status">
                        <option value="sale">sale</option>
                        <option value="out">out</option>
                        <option value="hidden">hidden</option>
                        <option value="dele">deleted</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" id="quantity" @click.prevent="updateProduct()">Update</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click.prevent="closeEditModal()">Close</button>
        </div>
        </div>
    </div>
    </div>
</div>
<script>
const { createApp, ref } = Vue;
const Swal = SweetAlert;
const baseUrl = "https://api.salla.dev/admin/v2/";
const app = createApp({
  data() {
    return {
      products: {},
      product: {},
      data: {},
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ory_at_nc-kEE5TVLmtu1HCm3jsUPGuvIometGFzA4OLNvH9m0.s7exocu9Q998OLQRsZToh1_P045VDLHE9bqa-Y7DFiU'
      },
      errors: [],
      status: "",
      perPage: "",
      search: "",
      page: 1,
      metadata: {
        current_page: 1,
        from: null,
        to: null,
        total: null,
        last_page: null,
      }
    };
  },
  methods: {
    getProducts() {
      axios.get(baseUrl+'products?status='+this.status+'&&per_page='+this.perPage+'&&keyword='+this.search, {
        headers: this.headers
      })
        .then((response) => {
          this.products = response.data;
          this.data = response.data.data;
          let pagination = response.data.pagination;
          this.metadata.current_page = pagination.currentPage;
          this.metadata.from = pagination.currentPage;
          this.metadata.to = pagination.total;
          this.metadata.to = pagination.totalPages;
          console.log(this.data);
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
        });
    },
    deleteProduct(id) {
        Swal.fire({
        title: 'Warning!',
        text: 'Do you want to delete this record ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then((result) => {
        if (result.value) {
            axios.delete(baseUrl+'products/'+id, {
                headers: this.headers
            })
            .then((response) => {
                console.log(response.data.data.code);
                if(response.data.data.code == 202) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Product deleted successfully',
                        icon: 'success',
                        confirmButtonText: 'Yes'
                    })

                    this.getProducts()
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong',
                    icon: 'error',
                    confirmButtonText: 'Yes'
                })
            });
        }
      })
    },
    editProduct(product) {
        this.product.id = product.id;
        this.product.name = product.name;
        this.product.price = Number(product.regular_price.amount),
        this.product.quantity = product.quantity
        this.product.status = product.status,
        $("#editProductModal").modal("show");
    },
    updateProduct() {
      this.errors = []
      axios.put(baseUrl+'products/'+this.product.id, this.product, {
        headers: this.headers
      })
        .then((response) => {
            console.log(response.data.status);
            if (response.data.status == 201) {
                $("#editProductModal").modal("hide");
                Swal.fire({
                    title: 'Success!',
                    text: 'Product updated successfully',
                    icon: 'success',
                    confirmButtonText: 'Yes'
                })
                this.getProducts();
            } else {
                $("#editProductModal").modal("hide");
                Swal.fire({
                    title: 'Error!',
                    text: response.data.data.error,
                    icon: 'error',
                    confirmButtonText: 'Yes'
                })
            }
        })
        .catch((error) => {
            var self = this;
            if (error.response.status === 422) {
                var fields = error.response.data.error.fields;
                _.each(fields, function(errors) {
                    _.each(errors, function(err) {
                       self.errors.push(err);
                    });
                });
            }
        });
    },
    closeEditModal() {
        $("#editProductModal").modal("hide");
        this.errors = []
        this.product = {}
    }
  },
  created() {
    this.getProducts()
  },
//   computed: {
//     filteredProducts() {
//         return this.data.filter(product => {
//             return product.name.toLowercase().indexOf(this.search.toLowercase()) != -1;
//         });
//     }
//   }
});

// Mount the app to the '#app' element
app.mount('#app');
</script>
</body>
</html>

<?php } else {
    header('Location: auth.php');
    exit;
}
?>