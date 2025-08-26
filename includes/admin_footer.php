<footer class="admin-footer">
  <div class="admin-footer-container">
    <p>&copy; <?php echo date('Y'); ?> Barangay Gumaoc East Admin Panel. All rights reserved.</p>
  </div>
</footer>

<style>
.admin-footer {
  background: #1b5e20;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 3rem;
  font-size: 0.85rem;
  padding: 1.5rem 0;
}

.admin-footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  text-align: center;
}

.admin-footer p {
  margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-footer-container {
    padding: 0 1.5rem;
  }
}

@media (max-width: 480px) {
  .admin-footer-container {
    padding: 0 1rem;
  }
  
  .admin-footer {
    padding: 1rem 0;
  }
}
</style>

</body>
</html>