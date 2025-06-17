<div style="display: flex; flex-direction: column; min-height: 100vh;">
<?php
$page_title = 'Цены';
$additional_css = 'price.css';
include 'includes/header.php';
?>

<div class="main-content">
    <div class="price-container">
        <table>
            <thead>
                <tr>
                    <th>Арена</th>
                    <th colspan="2">Standart</th>
                    <th colspan="2">V I P</th>
                    <th colspan="2">Сцена</th>
                </tr>
                <tr>
                    <th></th>
                    <th>Будни</th>
                    <th>Выходные</th>
                    <th>Будни</th>
                    <th>Выходные</th>
                    <th>Будни</th>
                    <th>Выходные</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1 час</td>
                    <td>7</td>
                    <td>8</td>
                    <td>9</td>
                    <td>10</td>
                    <td>9</td>
                    <td>10</td>
                </tr>
                <tr>
                    <td>3 часа</td>
                    <td>19</td>
                    <td>22</td>
                    <td>25</td>
                    <td>28</td>
                    <td>25</td>
                    <td>28</td>
                </tr>
                <tr>
                    <td>5 часов</td>
                    <td>31</td>
                    <td>36</td>
                    <td>40</td>
                    <td>45</td>
                    <td>40</td>
                    <td>45</td>
                </tr>
            </tbody>
        </table>
        <div class="price">
            <table>
                <thead>
                    <tr>
                        <th>Клуб</th>
                        <th colspan="2">Standart</th>
                        <th colspan="2">V I P</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Будни</th>
                        <th>Выходные</th>
                        <th>Будни</th>
                        <th>Выходные</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1 час</td>
                        <td>6</td>
                        <td>7</td>
                        <td>7</td>
                        <td>8</td>
                    </tr>
                    <tr>
                        <td>3 часа</td>
                        <td>16</td>
                        <td>19</td>
                        <td>19</td>
                        <td>22</td>
                    </tr>
                    <tr>
                        <td>5 часов</td>
                        <td>25</td>
                        <td>30</td>
                        <td>30</td>
                        <td>35</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
</div> 