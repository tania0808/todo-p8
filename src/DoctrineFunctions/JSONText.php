<?php

namespace App\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class JSONText extends FunctionNode
{
    private $expr;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            "CAST(%s AS TEXT)",
            $this->expr->dispatch($sqlWalker)
        );
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->expr = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}