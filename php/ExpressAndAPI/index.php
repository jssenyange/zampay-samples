<? require_once ("zampayfunctions.php"); ?>
<html>
<body>
    <?
    if(isset($_SESSION['checkout_errors'])){ 
    ?>
        <div style="color:red;"><?=$_SESSION['checkout_errors']?></div>
    <?
        unset($_SESSION['checkout_errors']);
    }
    ?>
	<form action='checkout.php' METHOD='POST'>
		<table>
        	<tbody>
                <tr>
                    <th colspan="2">
                        Select your fruits
                    </th>
                </tr>
                <tr>
                    <td style="width:30px">
                        <input id="mellon" type="checkbox" value="package_1" name="fruit[]">
                    </td>
                    <td>
                        <label for="mellon">
                            Water Mellon Costing ZMW 10 
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="mangoes" type="checkbox" value="package_2" name="fruit[]">
                    </td>
                    <td>
                        <label for="mangoes">
                            Mangoes Costing ZMW 20
                        </label> 
                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="oranges" type="checkbox" value="package_3" name="fruit[]">
                    </td>
                    <td>
                        <label for="oranges">
                            Oranges Costing ZMW 40
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
		<input type="submit" name='submit'  name='check-out-btn' value='Check out with Zampay'/>
	</form>
</body>
</html>
