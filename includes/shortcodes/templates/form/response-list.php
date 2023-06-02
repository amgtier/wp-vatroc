<?php
    $permalink = get_permalink( get_the_ID() );
    $user_version = [];
?>
<?php if ($view_all): ?>
<a href="<?php echo $permalink; ?>?view_all" class="btn btn-success">All Submissions</a>
<?php endif; ?>
<div>
    <table>
        <thead>
            <th></th>
            <th>Submitter</th>
            <th>Time</th>
            <?php foreach( $field_names as $name => $_ ): ?>
                <th><?php echo $name; ?></th>
            <?php endforeach; ?>
        </thead>
        <tbody>
            <?php foreach( $submissions as $idx => $obj ): ?>
                <?php $user_version[ $obj[ "uid" ] ] = isset( $user_version[ $obj[ "uid" ] ] ) ? $user_version[ $obj[ "uid" ] ] + 1 : 1;  ?>
                <tr>
                    <td>
                        <a href="<?php echo $permalink; ?>?view&v=<?php echo $user_version[ $obj[ "uid" ] ]; ?>&u=<?php echo $obj[ "uid" ]; ?>">
                            <button type="button">
                                <?php echo $idx + 1; ?>
                            </button>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $permalink; ?>?view_all&u=<?php echo $obj[ "uid" ]; ?>"><?php echo VATROC_My::html_my_avatar( intval( $obj[ "uid" ] ) ); ?></a>
                    </td>
                    <td><?php echo date( "Y/m/d H:i:s T",  $obj[ "timestamp" ] ); ?></td>
                    <?php foreach( $field_names as $name => $_ ): ?>
                        <td>
                            <?php echo $obj[ $name ]; ?>
                        </td>
                    <?php endforeach; ?>
                    <?php foreach( $options as $_ => $option ): ?>
                    <td>
                        <?php echo $option; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php if ($version === $user_version[ $obj[ "uid" ] ]){
                    echo "</tbody></table>";
                    echo $view_form;
                    echo "<table><tbody>";
                }?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>