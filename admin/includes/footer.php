            </div> <!-- End of main content -->
        </div> <!-- End of content wrapper -->
    </div> <!-- End of wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    
    <!-- Custom JS -->
    <script src="../assets/js/admin.js"></script>
    
    <script>
        $(document).ready(function () {
            // Toggle sidebar
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
            
            // Initialize DataTables
            $('.data-table').DataTable({
                responsive: true
            });
            
            // Activate tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>