<?php echo "Initial page" ?>
<h2>abcd</h2>

<h1>registaion<h1>
<?php  
            if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php
             endif;
         ?>