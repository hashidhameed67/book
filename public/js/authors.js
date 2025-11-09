$(document).ready(function(){

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    let dt;

function initTable(data){
    if ($.fn.DataTable.isDataTable('#authors-table')) {
        dt.clear().rows.add(data).draw();
        return;
    }
    dt = $('#authors-table').DataTable({
        data: data,
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'email' },
            { data: null, render: function(row){
                return row.books.map(b => b.name + " (₹" + b.price + ")").join("<br>");
            }},
            { data: null, orderable:false, render:function(row){
                return `
                    <button class="btn btn-sm btn-warning btn-edit" data-id="${row.id}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                `;
            }}
        ]
    });
}


    function fetchAuthors(query = ''){
        $.getJSON('/api/authors', function(res){
            if(res.success){
                let rows = res.data;
                if(query){
                    rows = rows.filter(a => a.name.toLowerCase().includes(query.toLowerCase()));
                }
                initTable(rows);
            } else {
                Swal.fire('Error', 'Could not fetch authors', 'error');
            }
        });
    }

    function resetForm(){
        $('#author-id').val('');
        $('#author-name').val('');
        $('#author-email').val('');
        $('#books-container').empty();
        addBookRow();
    }

    function addBookRow(book = {}) {
        const idField = book.id ? '<input type="hidden" name="books[][id]" value="'+book.id+'">' : '';
        const html = `<div class="book-row">
            ${idField}
            <input type="text" name="books[][name]" placeholder="Book name" value="${book.name || ''}" required>
            <input type="number" step="0.01" min="0.01" name="books[][price]" placeholder="Price" value="${book.price || ''}" required>
            <button type="button" class="btn-remove btn">❌ Remove</button>
        </div>`;
        $('#books-container').append(html);
    }

    // initial
    resetForm();
    fetchAuthors();


    $('#add-book').on('click', function(){ addBookRow(); });

   
    $(document).on('click', '.btn-remove', function(){
        $(this).closest('.book-row').remove();
    });


    $('#author-form').on('submit', function(e){
        e.preventDefault();
        const id = $('#author-id').val();
        const url = id ? '/api/authors/' + id : '/api/authors';
        const method = id ? 'PUT' : 'POST';

       
        const form = $(this);
        const payload = { name: $('#author-name').val(), email: $('#author-email').val(), books: [] };
        $('#books-container .book-row').each(function(){
            const name = $(this).find('input[name="books[][name]"]').val();
            const price = $(this).find('input[name="books[][price]"]').val();
            const bid = $(this).find('input[name="books[][id]"]').val();
            if(!name || !price) return;
            const obj = { name: name, price: parseFloat(price) };
            if(bid) obj.id = parseInt(bid);
            payload.books.push(obj);
        });

       
        if(payload.books.length === 0){
            Swal.fire('Validation', 'Add at least one book', 'warning');
            return;
        }
        for(let b of payload.books){
            if(b.price <= 0){ Swal.fire('Validation','Book price must be positive','warning'); return; }
        }

        $.ajax({
            url: url,
            method: method,
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res){
                if(res.success){
                    Swal.fire('Success', 'Saved successfully', 'success');
                    resetForm();
                    fetchAuthors($('#search-name').val());
                } else {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            },
            error: function(xhr){
                if(xhr.responseJSON && xhr.responseJSON.errors){
                    const errs = xhr.responseJSON.errors;
                    let msg = '';
                    Object.keys(errs).forEach(k => { msg += errs[k].join(', ') + '<br>'; });
                    Swal.fire({ title: 'Validation', html: msg, icon: 'error' });
                } else {
                    Swal.fire('Error', 'Server error', 'error');
                }
            }
        });
    });

  
    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');
        $.getJSON('/api/authors/' + id, function(res){
            if(res.success){
                const a = res.data;
                $('#author-id').val(a.id);
                $('#author-name').val(a.name);
                $('#author-email').val(a.email);
                $('#books-container').empty();
                a.books.forEach(b => addBookRow(b));
            } else {
                Swal.fire('Error', 'Author not found', 'error');
            }
        });
    });

   
    $(document).on('click', '.btn-delete', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the author and all related books.',
            icon: 'warning',
            showCancelButton: true
        }).then(result=>{
            if(result.isConfirmed){
                $.ajax({
                    url: '/api/authors/' + id,
                    method: 'DELETE',
                    success: function(res){
                        if(res.success){
                            Swal.fire('Deleted', res.message || 'Deleted', 'success');
                            fetchAuthors($('#search-name').val());
                        } else {
                            Swal.fire('Error', 'Could not delete', 'error');
                        }
                    },
                    error: function(){ Swal.fire('Error', 'Server error', 'error'); }
                });
            }
        });
    });

  
    $('#search-btn').on('click', function(){ fetchAuthors($('#search-name').val()); });
});