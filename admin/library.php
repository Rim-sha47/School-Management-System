<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch lists
try {
    // Books inventory
    $booksStmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
    $books = $booksStmt->fetchAll();

    // Borrow history
    $issuesStmt = $pdo->query("SELECT bi.*, b.title, b.author, s.first_name, s.last_name, s.registration_number 
                               FROM book_issues bi 
                               JOIN books b ON bi.book_id = b.id 
                               JOIN students s ON bi.student_id = s.id 
                               ORDER BY bi.id DESC");
    $issues = $issuesStmt->fetchAll();

    // Students list for issuing dropdown
    $studentsStmt = $pdo->query("SELECT id, first_name, last_name, registration_number FROM students ORDER BY first_name ASC");
    $students = $studentsStmt->fetchAll();

    // Available books for issuing dropdown
    $availBooksStmt = $pdo->query("SELECT id, title, author FROM books WHERE available_copies > 0 ORDER BY title ASC");
    $avail_books = $availBooksStmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    $books = [];
    $issues = [];
    $students = [];
    $avail_books = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Library Management";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search library logs...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Library Management</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Library</li>
        </ol>
      </div>
      <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addBookModal">
          <i class="fas fa-plus me-1"></i> Add Book
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueBookModal">
          <i class="fas fa-book-reader me-1"></i> Issue Book
        </button>
      </div>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Tabs Container -->
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="libraryTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active border-0 bg-transparent fw-semibold" id="books-tab" data-bs-toggle="tab" data-bs-target="#booksTab" type="button">Book Inventory</button>
      </li>
      <li class="nav-item">
        <button class="nav-link border-0 bg-transparent fw-semibold" id="issues-tab" data-bs-toggle="tab" data-bs-target="#issuesTab" type="button">Issued Books Log</button>
      </li>
    </ul>

    <div class="tab-content" id="libraryTabContent">
      <!-- Books Inventory Tab -->
      <div class="tab-pane fade show active" id="booksTab">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-book text-primary"></i> Catalog Directory</h5>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="booksTable">
<thead>
                 <tr>
                   <th>ID</th>
                   <th>Title</th>
                   <th>Author</th>
                   <th>ISBN</th>
                   <th>Publisher</th>
                   <th>Shelf Location</th>
                   <th>Available / Total Copies</th>
                   <th>Actions</th>
                 </tr>
               </thead>
              <tbody>
                <?php if(empty($books)): ?>
                  <tr><td colspan="7" class="text-center py-4">No books found in the catalog.</td></tr>
                <?php else: ?>
                  <?php foreach($books as $b): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($b['id']); ?></td>
                      <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                      <td><?php echo htmlspecialchars($b['author'] ?? 'N/A'); ?></td>
                      <td><span class="badge bg-light text-dark border font-monospace"><?php echo htmlspecialchars($b['isbn'] ?? 'N/A'); ?></span></td>
                      <td><?php echo htmlspecialchars($b['publisher'] ?? 'N/A'); ?></td>
                      <td><span class="badge bg-primary-pale text-primary border"><?php echo htmlspecialchars($b['shelf_location'] ?? 'N/A'); ?></span></td>
<td>
                         <strong><?php echo htmlspecialchars($b['available_copies']); ?></strong> / <?php echo htmlspecialchars($b['total_copies']); ?>
                       </td>
                       <td>
                         <button class="btn btn-sm btn-outline-primary btn-edit-book" data-id="<?php echo $b['id']; ?>" data-title="<?php echo htmlspecialchars($b['title']); ?>" data-author="<?php echo htmlspecialchars($b['author'] ?? ''); ?>" data-isbn="<?php echo htmlspecialchars($b['isbn'] ?? ''); ?>" data-publisher="<?php echo htmlspecialchars($b['publisher'] ?? ''); ?>" data-shelf="<?php echo htmlspecialchars($b['shelf_location'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                         <button class="btn btn-sm btn-outline-danger btn-delete-book" data-id="<?php echo $b['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                       </td>
                     </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Issued Log Tab -->
      <div class="tab-pane fade" id="issuesTab">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-history text-primary"></i> Lending Records</h5>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="issuesTable">
              <thead>
                <tr>
                  <th>Log ID</th>
                  <th>Book Title</th>
                  <th>Borrower Student</th>
                  <th>Issue Date</th>
                  <th>Due Date</th>
                  <th>Return Date</th>
                  <th>Fine (PKR)</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($issues)): ?>
                  <tr><td colspan="9" class="text-center py-4">No issue history found.</td></tr>
                <?php else: ?>
                  <?php foreach($issues as $is): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($is['id']); ?></td>
                      <td>
                        <strong><?php echo htmlspecialchars($is['title']); ?></strong>
                        <div class="small text-muted">by <?php echo htmlspecialchars($is['author']); ?></div>
                      </td>
                      <td>
                        <strong><?php echo htmlspecialchars($is['first_name'] . ' ' . $is['last_name']); ?></strong>
                        <div class="small text-muted font-monospace"><?php echo htmlspecialchars($is['registration_number']); ?></div>
                      </td>
                      <td><?php echo htmlspecialchars($is['issue_date']); ?></td>
                      <td><?php echo htmlspecialchars($is['due_date']); ?></td>
                      <td><?php echo htmlspecialchars($is['return_date'] ?? 'Not Returned Yet'); ?></td>
                      <td>PKR <?php echo number_format($is['fine']); ?></td>
                      <td>
                        <span class="status-badge <?php echo $is['status'] === 'returned' ? 'active' : ($is['status'] === 'overdue' ? 'overdue' : 'pending'); ?>">
                          <?php echo ucfirst(htmlspecialchars($is['status'])); ?>
                        </span>
                      </td>
                      <td>
                        <?php if($is['status'] === 'issued'): ?>
                          <button class="btn btn-sm btn-outline-success btn-return-book" data-id="<?php echo $is['id']; ?>" title="Mark Returned"><i class="fas fa-undo"></i> Return</button>
                        <?php else: ?>
                          <span class="text-muted small"><i class="fas fa-check-double text-success"></i> Log closed</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Book Modal -->
  <div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add Book to Library</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addBookForm">
            <div id="bookAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Book Title *</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Author *</label>
                <input type="text" name="author" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control" placeholder="e.g. 978-xxxxxxxxxx">
              </div>
              <div class="col-md-6">
                <label class="form-label">Publisher</label>
                <input type="text" name="publisher" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g. Textbook, Science">
              </div>
              <div class="col-md-4">
                <label class="form-label">Shelf Location</label>
                <input type="text" name="shelf_location" class="form-control" placeholder="e.g. A-02">
              </div>
              <div class="col-md-4">
                <label class="form-label">Total Copies *</label>
                <input type="number" name="total_copies" class="form-control" min="1" value="1" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Publish Year</label>
                <input type="number" name="publish_year" class="form-control" placeholder="e.g. 2023" min="1900" max="2026">
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveBookBtn">Save Book</button>
            </div>
          </form>
        </div>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Book Modal -->
   <div class="modal fade" id="editBookModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Book</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editBookForm">
             <div id="editBookAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editBookId">
             
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Book Title *</label>
                 <input type="text" name="title" id="editBookTitle" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Author *</label>
                 <input type="text" name="author" id="editBookAuthor" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">ISBN</label>
                 <input type="text" name="isbn" id="editBookIsbn" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">Publisher</label>
                 <input type="text" name="publisher" id="editBookPublisher" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">Shelf Location</label>
                 <input type="text" name="shelf_location" id="editBookShelf" class="form-control">
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateBookBtn">Update Book</button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>

   <!-- Issue Book Modal -->
  <div class="modal fade" id="issueBookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-book-reader me-2"></i> Issue Book to Student</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="issueBookForm">
            <div id="issueAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Select Book *</label>
                <select name="book_id" class="form-select" required>
                  <option value="">-- Select Book --</option>
                  <?php foreach($avail_books as $ab): ?>
                    <option value="<?php echo $ab['id']; ?>">
                      <?php echo htmlspecialchars($ab['title'] . ' (by ' . $ab['author'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Select Student *</label>
                <select name="student_id" class="form-select" required>
                  <option value="">-- Select Student --</option>
                  <?php foreach($students as $st): ?>
                    <option value="<?php echo $st['id']; ?>">
                      <?php echo htmlspecialchars($st['first_name'] . ' ' . $st['last_name'] . ' (' . $st['registration_number'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Issue Date *</label>
                <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Due Date *</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveIssueBtn">Issue Book</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script>
    // Search functionality
    document.getElementById('globalSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let activeTab = document.querySelector('#libraryTabs button.active').id;
      let targetTable = activeTab === 'books-tab' ? '#booksTable' : '#issuesTable';
      
      let rows = document.querySelectorAll(targetTable + ' tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Add Book AJAX
    document.getElementById('addBookForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveBookBtn');
      const alertDiv = document.getElementById('bookAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      fetch('add_book_action.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = data.message;
          setTimeout(() => { window.location.reload(); }, 1000);
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'A network error occurred.';
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    });

    // Issue Book AJAX
    document.getElementById('issueBookForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveIssueBtn');
      const alertDiv = document.getElementById('issueAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Issuing...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      fetch('issue_book_action.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = data.message;
          setTimeout(() => { window.location.reload(); }, 1000);
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'A network error occurred.';
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    });

// Return Book AJAX
     document.querySelectorAll('.btn-return-book').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Mark this book as returned?')) {
           const issueId = this.dataset.id;
           
           fetch('return_book_action.php', {
             method: 'POST',
             headers: {
               'Content-Type': 'application/x-www-form-urlencoded',
             },
             body: 'id=' + encodeURIComponent(issueId)
           })
           .then(res => res.json())
           .then(data => {
             if (data.status === 'success') {
               window.location.reload();
             } else {
               alert(data.message);
             }
           })
           .catch(err => {
             alert('A network error occurred.');
           });
         }
       });
     });

     // Edit Book Modal
     document.querySelectorAll('.btn-edit-book').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editBookId').value = this.dataset.id;
         document.getElementById('editBookTitle').value = this.dataset.title;
         document.getElementById('editBookAuthor').value = this.dataset.author;
         document.getElementById('editBookIsbn').value = this.dataset.isbn;
         document.getElementById('editBookPublisher').value = this.dataset.publisher;
         document.getElementById('editBookShelf').value = this.dataset.shelf;
         new bootstrap.Modal(document.getElementById('editBookModal')).show();
       });
     });

     // Update Book AJAX
     document.getElementById('editBookForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateBookBtn');
       const alertDiv = document.getElementById('editBookAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_book_action.php', {
         method: 'POST',
         body: new FormData(this)
       })
       .then(res => res.json())
       .then(data => {
         if(data.status === 'success') {
           alertDiv.className = 'alert alert-success';
           alertDiv.textContent = data.message;
           setTimeout(() => { window.location.reload(); }, 1000);
         } else {
           alertDiv.className = 'alert alert-danger';
           alertDiv.textContent = data.message;
           btn.innerHTML = originalText;
           btn.disabled = false;
         }
       })
       .catch(err => {
         alertDiv.className = 'alert alert-danger';
         alertDiv.textContent = 'A network error occurred.';
         btn.innerHTML = originalText;
         btn.disabled = false;
       });
     });

     // Delete Book AJAX
     document.querySelectorAll('.btn-delete-book').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this book?')) {
           const bookId = this.dataset.id;
           fetch('delete_book_action.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
             body: 'id=' + encodeURIComponent(bookId)
           })
           .then(res => res.json())
           .then(data => { if(data.status === 'success') { window.location.reload(); } else { alert(data.message); } })
           .catch(() => { alert('A network error occurred.'); });
         }
       });
     });
   </script>
</body>
</html>
