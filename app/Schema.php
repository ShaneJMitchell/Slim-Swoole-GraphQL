<?php

use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\StringType;
use queries\Author as AuthorQuery;
use queries\Authors as AuthorsQuery;
use queries\Quote as QuoteQuery;
use queries\Quotes as QuotesQuery;
use mutations\CreateAuthor as CreateAuthorMutation;

//$queryType = new ObjectType([
//    'name' => 'Query',
//    'fields' => [
//        'message' => [
//            'type' => Type::string(),
//            'resolve' => function () {
//                return 'foo';
//            }
//        ]
//    ]
//]);


return new Schema(SchemaConfig::create([
    'query' => new ObjectType([
        'name' => 'Query',
        'fields' => [
            'message' => [
                'type' => Type::string(),
                'resolve' => function () {
                    return 'foo';
                }
            ],
            'blah' => [
                'type' => Type::string(),
                'resolve' => function () {
                    return 'blah';
                }
            ],
            'test' => [
                'type' => Type::string(),
                'resolve' => function () {
                    return 'test';
                }
            ]
//            'author' => AuthorQuery::get(),
//            'authors' => AuthorsQuery::get(),
//            'quote' => QuoteQuery::get(),
//            'quotes' => QuotesQuery::get(),
        ]
    ]),
//    'mutation' => new ObjectType([
//        'name' => 'Mutation',
//        'fields' => [
//            'sum' => function ($root, $args) {
//                return $args['a'] + $args['b'];
//            },
////            'createAuthor' => CreateAuthorMutation::get(),
//        ]
//    ])
]));