<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Authors & Books</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/style.css">



</head>
<body>
    <div class="wrapper">
        <h2> Books</h2>

        <form id="author-form">
            <input type="hidden" name="id" id="author-id">

             <div class="row mb-3">
                <div class="col-md-6">
                    <label for="author-name" class="form-label fw-semibold">Author Name</label>
                    <input type="text" name="name" id="author-name" class="form-control" placeholder="Enter author name" required>
                </div>

                <div class="col-md-6">
                    <label for="author-email" class="form-label fw-semibold">Author Email</label>
                    <input type="email" name="email" id="author-email" class="form-control" placeholder="Enter author email" required>
                </div>
            </div>

            <div class="books-section">
                <h4 style="margin-top:0;">Books</h4>
                <div id="books-container"></div>
                <button type="button" id="add-book" class="btn btn-success">➕ Add Another Book</button>
            </div>

            <div class="actions">
                <button type="submit" id="submit-btn" class="btn btn-primary"> Save Author</button>
            </div>
        </form>

        <hr>

        <h3>Authors List</h3>


        <table id="authors-table" class="display">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Books</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/authors.js"></script>
    
</body>
</html>
